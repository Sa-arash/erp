<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use App\Filament\Admin\Resources\AssetResource;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseRequestItem;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ApprovePurchase extends ManageRelatedRecords
{
    protected static string $resource = ApprovalResource::class;

    protected static string $relationship = 'approvable';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Approvable';
    }
    public static function canAccess(array $parameters = []): bool
    {
        return true; // TODO: Change the autogenerated stub
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                    Select::make('product_id')
                        ->label('Product')->options(function () {
                            $products = getCompany()->products;
                            $data = [];
                            foreach ($products as $product) {
                                $data[$product->id] = $product->title . " (" . $product->sku . ")";
                            }
                            return $data;
                        })->required()->searchable()->preload(),
                    TextInput::make('description')->label('Description')->required(),

                    Select::make('unit_id')->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                    TextInput::make('quantity')->required()->live()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    TextInput::make('estimated_unit_cost')->label('Estimated Unit Cost')->live()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    Select::make('project_id')->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                    Placeholder::make('total')->content(fn($state, Get $get) => number_format(((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost'))))),
                    Placeholder::make('stock in')->content(function ($record, Get $get) {
                        $products = Product::find($get('product_id'))->assets->where('status', 'inStorageUsable')->count();
                        $url = AssetResource::getUrl('index', ['tableFilters[product_id][value]' => $get('product_id'), 'tableFilters[status][value]' => 'inStorageUsable']);
                        return new HtmlString("<a style='color: #1cc6b9' target='_blank' href='{$url}'>$products</a>");
                    }),
                    TextInput::make('comment')->columnSpan(6),
                    Forms\Components\ToggleButtons::make('decision')->grouped()->inline()->columnSpan(2)->options(['approve' => 'Approve', 'reject' => 'Reject'])->required()->colors(['approve' => 'success', 'reject' => 'danger']),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table->query(PurchaseRequestItem::query()->where('purchase_request_id',$this->record->approvable->id))
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('product.info'),
                Tables\Columns\TextColumn::make('unit.title'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('estimated_unit_cost')->label('EUC'),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('clarification_decision')->badge()->state(fn($record)=>match ($record->clarification_decision){
                    'approve' => 'Approved',
                    'reject' => 'Rejected',
                    default => 'Pending',
                })->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'primary',
                    })->badge()->label('Warehouse Decision'),
                Tables\Columns\TextColumn::make('clarification_comment')->limit(50)->tooltip(fn($record) => $record->clarification_comment)->label('Warehouse Comment'),
                Tables\Columns\TextColumn::make('verification_decision')->badge()->state(fn($record)=>match ($record->verification_decision){
                    'approve' => 'Approved',
                    'reject' => 'Rejected',
                    default => 'Pending',
                })->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'primary',
                    })->badge()->label('Verification Decision'),
                Tables\Columns\TextColumn::make('verification_comment')->limit(50)->tooltip(fn($record) => $record->verification_comment)->label('Verification Comment'),
                Tables\Columns\TextColumn::make('approval_decision')->badge()->state(fn($record)=>match ($record->approval_decision){
                        'approve' => 'Approved',
                        'reject' => 'Rejected',
                        default => 'Pending',
                    })->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'primary',
                    })->label('Approval Decision'),
                Tables\Columns\TextColumn::make('approval_comment')->limit(50)->tooltip(fn($record) => $record->approval_comment)->label('Approval Comment'),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('document')->label('View Attachment')->url(function ($record){
                    if (isset($record->media[0])){
                        return $record->media[0]->original_url;
                    }
                })->state(fn($record)=> isset($record->media[0])? 'Attach File':null)->color('warning')->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('Approve')->label('Approved Or Rejected')->color('success')->form([
                    Forms\Components\Section::make([
                        Forms\Components\Section::make([
                            Select::make('employee')->disabled()->default(fn($record) => $this->record?->approvable?->employee_id)->options(fn($record) => Employee::query()->where('id', $this->record?->approvable?->employee_id)->get()->pluck('info', 'id'))->searchable(),
                            Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger'])->options(['Approve' => 'Approved', 'NotApprove' => 'Not Approved'])->grouped(),
                            Forms\Components\ToggleButtons::make('is_quotation')->disabled($this->record->position==="PR Approval")->default($this->record?->approvable?->is_quotation)->required()->label('Need Quotation')->boolean(' With Quotation', 'With out Quotation')->grouped()->inline(),
                            Forms\Components\Textarea::make('comment')->nullable()->columnSpanFull(),
                        ])->columns(3),
                        Forms\Components\Repeater::make('items')->deletable(false)->formatStateUsing(function(){
                            $data=[];
                            foreach ($this->record?->approvable?->items?->toArray() as $item){
                                $item['decision']='approve';
                                $data[]=$item;
                            }

                         return $data;
                        })->schema([
                            Select::make('product_id')
                                ->label('Product')->options(function () {
                                    $products = getCompany()->products;
                                    $data = [];
                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                    }
                                    return $data;
                                })->required()->searchable()->preload(),
                            TextInput::make('description')->label('Description')->required(),
                            Select::make('unit_id')->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                            TextInput::make('quantity')->required()->live()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            TextInput::make('estimated_unit_cost')->label('Estimated Unit Cost')->live()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Select::make('project_id')->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                            Placeholder::make('total')->content(fn($state, Get $get) => number_format(((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost'))))),
                            Placeholder::make('stock in')->content(function ($record, Get $get) {
                                $products = Product::find($get('product_id'))->assets->where('status', 'inStorageUsable')->count();
                                $url = AssetResource::getUrl('index', ['tableFilters[product_id][value]' => $get('product_id'), 'tableFilters[status][value]' => 'inStorageUsable']);
                                return new HtmlString("<a style='color: #1cc6b9' target='_blank' href='{$url}'>$products</a>");
                            }),
                            TextInput::make('comment')->columnSpan(6),
                            Forms\Components\ToggleButtons::make('decision')->grouped()->inline()->columnSpan(2)->options(['approve' => 'Approve', 'reject' => 'Reject'])->required()->colors(['approve' => 'success', 'reject' => 'danger']),
                        ])->columns(8)->columnSpanFull()->addable(false)->orderable(false)
                    ])->columns(),
                ])->modalWidth(MaxWidth::Full)->action(function ($data) {

                    $record=$this->record;
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);
                    $PR = $record->approvable;
                    if (!isset($data['is_quotation'])){
                        $data['is_quotation']=$PR->is_quotation;
                    }
                    $PR->approvals()->whereNot('id', $record->id)->where('position', $record->position)->delete();
                    if ($data['status'] === "NotApprove") {
                        $PR->update(['is_quotation' => $data['is_quotation'], 'status' => "Rejected"]);
                    } else {
                        if ($PR->status->name === "Requested") {
                            $PR->update(['is_quotation' => $data['is_quotation'], 'status' => 'Clarification']);

                        } else if ($PR->status->name === "Clarification") {
                            $PR->update(['is_quotation' => $data['is_quotation'], 'status' => 'Verification']);
                        } elseif ($PR->status->name === "Verification") {
                            $PR->update(['is_quotation' => $data['is_quotation'], 'status' => 'Approval']);
                        }
                    }
                    foreach ($data['items'] as $item) {
                        $item['status'] = $item['decision'] === "reject" ? "rejected" : "approve";
                        if ($PR->status->name === "Clarification") {
                            $item['clarification_comment'] = $item['comment'];
                            $item['clarification_decision'] = $item['decision'];
                        } else if ($PR->status->name === "Verification") {
                            $item['verification_comment'] = $item['comment'];
                            $item['verification_decision'] = $item['decision'];
                        } elseif ($PR->status->name === "Approval") {
                            $item['approval_comment'] = $item['comment'];
                            $item['approval_decision'] = $item['decision'];
                        }
                        $prItem = PurchaseRequestItem::query()->firstWhere('id', $item['id']);
                        $prItem->update($item);

                    }
                    if ($data['status'] === "Approve") {
                        if ($PR->status->name === "Clarification") {
                            sendApprove($PR, 'PR Verification_approval');
                        } else if ($PR->status->name === "Verification") {
                            sendApprove($PR, 'PR Approval_approval');
                        }
                    }
                    Notification::make('success')->success()->title('Answer '.$record->position.' PR NO : '.$PR->purchase_number)->send()->sendToDatabase(auth()->user());
                })->visible(function () {

                    if ($this->record->status->name !== "Approve") {
                        if (substr($this->record->approvable_type, 11) === "PurchaseRequest") {
                            return true;
                        }
                    }
                    return  false;
                })
            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                ]),
            ]);
    }
}
