<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Filament\Support\RawJs;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;
use function Filament\Support\is_app_url;

class Replicate extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;


    public function mount(): void
    {
        $this->fillForm();

        $this->previousUrl = url()->previous();

    }
    public static function canAccess(array $parameters = []): bool
    {
        $url=request('tk');
        if ($url){
            if ($url==="my"){
                $PR=PurchaseRequest::query()->where('employee_id',getEmployee()->id)->where('id',request('id'))->first();
                if ($PR)
                    return true ;
            }elseif ($url==="resource"){
                return true;
            }
        }
        return false;
    }
    public function mountCanAuthorizeResourceAccess(): void
    {
    }

    public static function authorizeResourceAccess(): void
    {
    }

    public function create(bool $another = false): void
    {

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canCreate(), 403);
    }

    public function afterFill()
    {
        $PR = PurchaseRequest::query()->with(['items', 'items.media'])->firstWhere('id', request('id'));
        if (!$PR) {
            abort(404);
        }
        $PR = $PR->toArray();
        $puncher = PurchaseRequest::query()->where('company_id', getCompany()->id)->latest()->first();
        if ($puncher) {
            $PR['purchase_number'] = generateNextCodePO($puncher->purchase_number);
        }
        $PR['request_date'] = now()->format('Y-m-d H:i:s');
        foreach ($PR['items'] as $key => $item) {
            $product = Product::query()->firstWhere('id', $item['product_id']);
            $PR['items'][$key]['department_id'] = $product->department_id;
            $PR['items'][$key]['document'] = $item['media'];
        }
        $PR['status']="Requested";
        $this->data = $PR;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('')->schema([
                Select::make('employee_id')->live()
                    ->searchable()
                    ->preload()
                    ->label('Requested By')
                    ->required()
                    ->options(getCompany()->employees->pluck('fullName', 'id'))
                    ->default(fn() => auth()->user()->employee->id),

                TextInput::make('purchase_number')->readOnly()
                    ->label('PR Number')->prefix('ATGT/UNC/')
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                        return $rule->where('company_id', getCompany()->id);
                    })
                    ->required()
                    ->numeric(),
                DateTimePicker::make('request_date')->readOnly()->default(now())->label('Request Date')->required(),
                Hidden::make('status')->label('Status')->default('Requested')->required(),
                Select::make('currency_id')->live()->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload(),
                TextInput::make('description')->label('Description')->columnSpanFull(),
                Repeater::make('items')
                    ->addActionLabel('Add')
                    ->relationship('items')
                    ->schema([
                        Select::make('department_id')->label('Section')->options(getCompany()->departments->pluck('title', 'id'))->searchable()->preload()->live(),
                        Select::make('product_id')->disableOptionsWhenSelectedInSiblingRepeaterItems()->label('Product/Service')
                            ->options(function (Get $get) {

                                if ($get('department_id')) {
                                    $data = [];
                                    $products = getCompany()->products->where('department_id', $get('department_id'))->pluck('title', 'id');
                                    $i = 1;
                                    foreach ($products as $key => $product) {

                                        $data[$key] = $i . ". " . $product;
                                        $i++;

                                    }
                                    return $data;
                                }
                            })->required()->searchable()->preload()->afterStateUpdated(function (Set $set, $state) {
                                $product = Product::query()->firstWhere('id', $state);
                                if ($product) {
                                    $set('unit_id', $product->unit_id);
                                }
                            })->afterStateHydrated(function ($state, Set $set) {
                                $product = Product::query()->firstWhere('id', $state);

                                $set('department_id', $product?->department_id);
                            })->live(true)->columnSpan(2),
                        Select::make('unit_id')->createOptionForm([
                            TextInput::make('title')->label('Unit Name')->unique('units', 'title')->required()->maxLength(255),
                            Toggle::make('is_package')->live()->required(),
                            TextInput::make('items_per_package')->numeric()->visible(fn(Get $get) => $get('is_package'))->default(null),
                        ])->createOptionUsing(function ($data) {
                            $data['company_id'] = getCompany()->id;
                            Notification::make('success')->success()->title('Create Unit')->send();
                            return Unit::query()->create($data)->getKey();
                        })->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                        TextInput::make('quantity')->required()->live()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        TextInput::make('estimated_unit_cost')
                            ->label('EST Unit Cost')->live(true)
                            ->numeric()->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),
                        Select::make('project_id')
                            ->searchable()
                            ->preload()
                            ->label('Project')
                            ->options(getCompany()->projects->pluck('name', 'id')),

                        Placeholder::make('total')
                            ->content(fn($state, Get $get) => number_format((((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost')))))),

                        Hidden::make('company_id')
                            ->default(Filament::getTenant()->id)
                            ->required(),
                        Textarea::make('description')
                            ->label(' Product Name And Description')
                            ->columnSpan(6)
                            ->required(),
                        MediaManagerInput::make('document')->orderable(false)->folderTitleFieldName("purchase_request_id")
                            ->disk('public')
                            ->schema([
                            ])->defaultItems(0)->maxItems(1)->columnSpan(2),
                    ])
                    ->columns(8)
                    ->columnSpanFull(),

            ])->columns(4)
        ]);
    }
    public function afterCreate(){
        $request=$this->record;
        sendApprove($request,'PR Warehouse/Storage Clarification_approval');
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl;
    }

}
