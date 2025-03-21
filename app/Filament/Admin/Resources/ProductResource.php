<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Account;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $pluralLabel = "Product";
    protected static ?string $label="Product (Logistic Setting)";

    protected static ?string $navigationIcon = 'heroicon-m-cube';
    protected static ?string $cluster = StackManagementSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\TextInput::make('title')->label('Product Name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('sku')->label(' SKU')
                        ->unique(ignoreRecord:true ,modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })->default(function () {
                            $product = Product::query()->where('company_id',getCompany()->id)->latest()->first();
                            if ($product) {
                                return generateNextCodeProduct($product->sku);
                            }
                        })
                    ->required()->maxLength(255),
                    Select::make('product_type')->searchable()->options(['consumable' => 'consumable', 'unConsumable' => 'unConsumable'])->default('consumable')
                    ->live()->afterStateUpdated(function(Set $set){
                        $set('account_id',null);
                    }),
                ])->columns(3),

                Select::make('account_id')->options(function (Get $get) {

                   if($get('product_type')=='unConsumable')
                   {

                    $data=[];
                    if (getCompany()->product_accounts){
                        $accounts= Account::query()
                            ->whereIn('id', getCompany()->product_accounts)
                            ->where('company_id', getCompany()->id)->get();
                    }else{
                        $accounts= Account::query()
                            ->where('company_id', getCompany()->id)->get();
                    }
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                }elseif($get('product_type')=='consumable'){
                    $data=[];
                    if (getCompany()->product_expence_accounts){
                        $accounts= Account::query()
                            ->whereIn('id', getCompany()->product_expence_accounts)
                            ->where('company_id', getCompany()->id)->get();
                    }else{
                        $accounts= Account::query()
                            ->where('company_id', getCompany()->id)->get();
                    }
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                }
                })->required()->model(Transaction::class)->searchable()->label('Category'),

                select::make('sub_account_id')->required()->searchable()->label('SubCategory')->options(fn(Get $get)=>  $get('account_id') !== null? getCompany()->accounts()->where('parent_id',$get('account_id'))->pluck('name', 'id'):[])
//                    ->createOptionForm([
//                        Forms\Components\Section::make([
//                            Forms\Components\TextInput::make('title')->label('Sub Category Name')->required()->maxLength(255),
////                            select::make('product_category_id')->searchable()->label('Parent')->required()->options(getCompany()->productCategories()->pluck('title', 'id')),
//
//                        ])
//                    ])
//                    ->createOptionUsing(function (array $data): int {
//                        return Account::query()->create([
//                            'title' => $data['title'],
//                            'product_category_id' => $data['product_category_id'],
//                            'company_id' => getCompany()->id
//                        ])->getKey();
//                    })
                ,

                Forms\Components\Textarea::make('description')->columnSpanFull(),
                MediaManagerInput::make('photo')->orderable(false)
                    ->disk('public')
                    ->schema([
                    ])->maxItems(1),
                Forms\Components\TextInput::make('stock_alert_threshold')->numeric()->default(5)->required(),

                // Forms\Components\TextInput::make('price')
                // ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                //     ->numeric()
                //     ->prefix('$'),
                // Forms\Components\DatePicker::make('expiration_date')->default(now()),
            ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('image')->defaultImageUrl(asset('img/images.jpeg'))->state(function ($record){
                    return $record->media->first()?->original_url;
                }),
                Tables\Columns\TextColumn::make('title')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category ')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category ')->sortable(),
                Tables\Columns\TextColumn::make('product_type'),
                Tables\Columns\TextColumn::make('count')->numeric()->state(fn($record) => $record->assets->count())->label('Quantity')->badge()
                ->color(fn($record)=>$record->assets->count()>$record->stock_alert_threshold ? 'success' : 'danger')->tooltip(fn($record)=>'Stock Alert:'.$record->stock_alert_threshold),
                Tables\Columns\TextColumn::make('price')->numeric()->state(fn($record) => $record->assets->sum('price'))->label('Total Value')->badge()->color('success')

            ])
            ->filters([

//                SelectFilter::make('unit_id')->searchable()->preload()->options(Unit::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
//                    ->label('Unit'),

            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssetsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}/view'),
        ];
    }
}
