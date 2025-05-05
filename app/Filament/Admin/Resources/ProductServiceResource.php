<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductServiceResource\Pages;
use App\Filament\Admin\Resources\ProductServiceResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Account;
use App\Models\Department;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Unit;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ImageEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class ProductServiceResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $label="Service";
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $pluralLabel = "Service";
    protected static ?string $cluster = StackManagementSettings::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\TextInput::make('title')->label('Service Name')->required()->maxLength(255),
                    Select::make('department_id')->live()->label('Department')->required()->options(getCompany()->departments->pluck('title','id'))->searchable()->preload()->afterStateUpdated(function (Get $get,Set $set,$state){
                        $department=Department::query()->firstWhere('id',$state);
                        if ($department){
                            $product = Product::query()->where('department_id',$state)->where('company_id',getCompany()->id)->latest('sku')->first();
                            if ($product) {
                                $set('sku',generateNextCodeProduct($product->sku));
                            }else{
                                $set('sku',$department->abbreviation."-0001");
                            }
                        }
                    }),
                    Forms\Components\TextInput::make('sku')->readOnly()->label(' SKU')->unique(ignoreRecord:true ,modifyRuleUsing: function (Unique $rule) {
                        return $rule->where('company_id', getCompany()->id);
                    })->required()->maxLength(255),
                    Select::make('unit_id')->required()->relationship('unit','title',fn($query)=>$query->where('company_id',getCompany()->id))->searchable()->preload()->createOptionForm([
                        Forms\Components\TextInput::make('title')->label('Unit Name')->unique('units', 'title')->required()->maxLength(255),
                        Forms\Components\Toggle::make('is_package')->live()->required(),
                        Forms\Components\TextInput::make('items_per_package')->numeric()->visible(fn(Get $get) => $get('is_package'))->default(null),
                    ])->createOptionUsing(function ($data) {
                        $data['company_id'] = getCompany()->id;
                        Notification::make('success')->success()->title('Create Unit')->send();
                        return  Unit::query()->create($data)->getKey();
                    }),
                    Select::make('account_id')->options(function (Get $get) {
                        $data = [];
                        if (getCompany()->product_service_accounts) {
                            $accounts = Account::query()
                                ->whereIn('id', getCompany()->product_service_accounts)
                                ->where('company_id', getCompany()->id)->get();
                        } else {
                            $accounts = Account::query()
                                ->where('company_id', getCompany()->id)->get();
                        }
                        foreach ($accounts as $account) {
                            $data[$account->id] = $account->name . " (" . $account->code . ")";
                        }
                        return $data;
                    }
                    )->required()->model(Transaction::class)->searchable()->label('Category'),
                    Select::make('sub_account_id')->label('SubCategory')->required()->options(function (Get $get){
                        $parent=$get('account_id');
                        if ($parent){
                            $accounts =  Account::query()
                                ->where('parent_id', $parent)
                                ->orWhereHas('account', function ($query) use ($parent) {
                                    return $query->where('parent_id', $parent)->orWhereHas('account', function ($query) use ($parent) {
                                        return $query->where('parent_id', $parent);
                                    });
                                })
                                ->get();
                            $data=[];
                            foreach ($accounts as $account){
                                $data[$account->id]=$account->title;
                            }
                            return $data;
                        }
                        return  [];
                    })->searchable(),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Section::make([
                    MediaManagerInput::make('photo')->columnSpan(1)->label('Upload Image')->image(true)->orderable(false)->disk('public')->schema([])->maxItems(1),
                ])->columns(4),
                Forms\Components\Hidden::make('product_type')->default('service')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->query(Product::query()->where('product_type','service'))
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('image')->defaultImageUrl(asset('img/images.jpeg'))->state(function ($record){
                    return $record->media->first()?->original_url;
                })->action(Tables\Actions\Action::make('image')->modalSubmitAction(false)->infolist(function ($record){
                    if ($record->media->first()?->original_url){
                        return  [
                            \Filament\Infolists\Components\Section::make([
                                ImageEntry::make('image')->label('')->width(650)->height(650)->columnSpanFull()->state($record->media->first()?->original_url)
                            ])
                        ];
                    }
                })),
                Tables\Columns\TextColumn::make('title')->label('Service Name')->searchable(),
                Tables\Columns\TextColumn::make('sku')->label('Service Code')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category ')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category ')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductServices::route('/'),
            'create' => Pages\CreateProductService::route('/create'),
            'edit' => Pages\EditProductService::route('/{record}/edit'),
        ];
    }
}
