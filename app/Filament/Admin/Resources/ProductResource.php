<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Account;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\ProductSubCategory;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\select;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?string $label = "Product";
    protected static ?string $navigationIcon = 'heroicon-m-cube';
    protected static ?string $cluster = StackManagementSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\TextInput::make('title')->label('Product Name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('sku')->label(' SKU')
                    ->unique(modifyRuleUsing: function (Unique $rule) {
                        return $rule->where('company_id', getCompany()->id);
                    })
                    ->required()->maxLength(255),
                    Select::make('product_type')->searchable()->options(['consumable' => 'consumable', 'unConsumable' => 'unConsumable'])->default('consumable'),
                ])->columns(3),
                Select::make('account_id')->options(function () {
                    return Account::query()
                    // ->whereIn('id', [46,178,179])
                    ->where('company_id', getCompany()->id)->pluck('name','id')->toArray();
                })->required()->model(Transaction::class)->searchable()->label('Categoy'),

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
                Forms\Components\FileUpload::make('image')->image(),
                Forms\Components\TextInput::make('stock_alert_threshold')->numeric()->default(5),

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
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('title')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category Title')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category Title')->sortable(),
                Tables\Columns\TextColumn::make('product_type'),
                Tables\Columns\TextColumn::make('count')->numeric()->state(fn($record) => $record->assets->count())->label('Quantity')->badge(),
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
