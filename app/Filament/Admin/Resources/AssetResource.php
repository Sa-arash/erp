<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetResource\Pages;
use App\Filament\Admin\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $navigationIcon = 'heroicon-s-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('assets')->schema([
                    Forms\Components\Select::make('product_id')->label('Product')->options(function () {
                        $products = getCompany()->products;
                        $data = [];
                        foreach ($products as $product) {
                            $data[$product->id] = $product->title . " (" . $product->sku . ")";
                        }
                        return $data;
                    })->required()->searchable()->preload(),
                    select::make('brand_id')->searchable()->label('Brand')->required()->options(getCompany()->brands->pluck('title', 'id'))
                        ->createOptionForm([
                            Forms\Components\Section::make([
                                Forms\Components\TextInput::make('title')->label('Brand Name')->required()->maxLength(255),
                            ])
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return brand::query()->create([
                                'title' => $data['title'],
                                'company_id' => getCompany()->id
                            ])->getKey();
                        }),
                    Forms\Components\TextInput::make('model')->nullable()->label('Model'),
                    Forms\Components\TextInput::make('number')->default(function (){
                        $asset=Asset::query()->where('company_id',getCompany()->id)->latest()->first();
                        if ($asset){
                          return  generateNextCodeAsset($asset->number);
                        }else{
                            return "0001";
                        }
                    })->required()->numeric()->label('Asset Number')->maxLength(50),
                    Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(50),
                    Forms\Components\TextInput::make('price')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),
                    Forms\Components\Select::make('warehouse_id')->live()->label('Warehouse/Building')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                    SelectTree::make('structure_id')->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    })->required(),
                    Forms\Components\Hidden::make('status')->default('inStorageUsable')->required(),
                    KeyValue::make('attributes')->keyLabel('title')->columnSpanFull(),


                ])->columns(4)->columnSpanFull()->cloneable()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('product.sku')->label('SKU')->searchable(),
                Tables\Columns\TextColumn::make('product.title')->label('Asset Name')->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Purchase Price')->sortable()->numeric(),
                Tables\Columns\TextColumn::make('status')->badge(),

                Tables\Columns\TextColumn::make('warehouse.title')->label('Warehouse/Building')->sortable(),
                Tables\Columns\TextColumn::make('structure.title')->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('employee')->state(function ($record) {
                    return $record->employees->last()?->assetEmployee?->employee?->fullName;
                })->badge()->url(function ($record) {
                    if ($record->employees->last()?->assetEmployee?->employee_id) {
                        return EmployeeResource::getUrl('view', ['record' => $record->employees->last()?->assetEmployee?->employee_id]);
                    }
                })->label('Employee'),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->searchable()->options(getCompany()->products->pluck('title','id'))->label('Product'),
                Tables\Filters\SelectFilter::make('status')->searchable()->options(['inuse' => "Inuse", 'inStorageUsable' => "InStorageUsable", 'storageUnUsable' => "StorageUnUsable", 'outForRepair' => 'OutForRepair', 'loanedOut' => "LoanedOut"]),
                Tables\Filters\Filter::make('tree')
                    ->form([
                        Forms\Components\Select::make('warehouse_id')->label('Warehouse')->options(getCompany()->warehouses()->pluck('title', 'id'))->searchable()->preload(),
                        SelectTree::make('structure_id')->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                            return $query->where('warehouse_id', $get('warehouse_id'));
                        })->required()
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['structure_id'], function ($query, $data) {
                            return $query->where('structure_id', $data);
                        });
                    })->columns(3)->columnSpanFull()

            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make()->form([
                    Forms\Components\Section::make([
                        Forms\Components\Select::make('product_id')->label('Product')->options(getCompany()->products()->pluck('title', 'id'))->required()->searchable()->preload(),
                        select::make('brand_id')->searchable()->label('Brand')->required()->options(getCompany()->brands->pluck('title', 'id'))
                            ->createOptionForm([
                                Forms\Components\Section::make([
                                    Forms\Components\TextInput::make('title')->label('Brand Name')->required()->maxLength(255),
                                ])
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return brand::query()->create([
                                    'title' => $data['title'],
                                    'company_id' => getCompany()->id
                                ])->getKey();
                            }),
                        Forms\Components\TextInput::make('model')->nullable()->label('Model'),
                        Forms\Components\TextInput::make('number')->required()->label('number')->label('Asset Number')->maxLength(50),
                        Forms\Components\TextInput::make('serial_number')->required()->label('Serial Number')->maxLength(50),

                        Forms\Components\TextInput::make('price')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),
                        Forms\Components\Select::make('warehouse_id')->live()->label('Warehouse')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                        SelectTree::make('structure_id')->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                            return $query->where('warehouse_id', $get('warehouse_id'));
                        })->required(),
                        Forms\Components\Select::make('status')->default('inStorageUsable')->options(['inuse' => "Inuse", 'inStorageUsable' => "InStorageUsable", 'storageUnUsable' => "StorageUnUsable", 'outForRepair' => "OutForRepair", 'loanedOut' => "LoanedOut"])->required()->searchable(),
                        KeyValue::make('attributes')->keyLabel('title')->columnSpanFull(),

                    ])->columns(3)
                ]),
                Tables\Actions\ViewAction::make()
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
            RelationManagers\EmployeesRelationManager::class,
            RelationManagers\FinanceRelationManager::class,
            RelationManagers\ServiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}/view'),
        ];
    }
}
