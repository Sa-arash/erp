<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetResource\Pages;
use App\Filament\Admin\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Asset/Inventory';
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
                    Forms\Components\TextInput::make('number')->default(function () {
                        $asset = Asset::query()->where('company_id', getCompany()->id)->latest()->first();
                        if ($asset) {
                            return generateNextCodeAsset($asset->number);
                        } else {
                            return "0001";
                        }
                    })->required()->numeric()->label('Asset Number')->maxLength(50),
                    Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(50),
                    Forms\Components\TextInput::make('price')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),
                    Forms\Components\Select::make('warehouse_id')->live()->label('Warehouse/Building')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                    SelectTree::make('structure_id')->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    })->required(),
                    DatePicker::make('buy_date')->default(now()),
                    DatePicker::make('garanry_date')->default(now()),
                    Forms\Components\Select::make('depreciation_years')
                        ->label('Depreciation Years')
                        ->options([
                            1 => '1 Year',
                            2 => '2 Years',
                            3 => '3 Years',
                            4 => '4 Years',
                            5 => '5 Years',
                        ])
                        ->default(1)
                        ->required(),

                    Forms\Components\TextInput::make('depreciation_amount')
                        ->label('Depreciation Amount')
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->required()
                        ->placeholder('Enter amount'),

                    Forms\Components\Hidden::make('status')->default('inStorageUsable')->required(),
                    KeyValue::make('attributes')->keyLabel('title')->columnSpanFull(),
                ])->columns(4)->columnSpanFull()->collapsed()->default(function () {
                    if (isset($_GET['po'])) {
                        $asset = Asset::query()->where('company_id', getCompany()->id)->latest()->first();
                        if ($asset) {
                            $number = generateNextCodeAsset($asset->number);
                        } else {
                            $number = "0001";
                        }
                        $PO = PurchaseOrder::query()->firstWhere('id', $_GET['po']);
                        $assets = [];
                        if ($PO) {
                            foreach ($PO->items as $item) {
                                for ($i = 0; $i <= $item->quantity; $i++) {
                                    $data = [];
                                    $freights = $item->freights === null ? 0 : (float) $item->freights;
                                    $q = 1;
                                    $tax = $item->taxes === null ? 0 : (float)$item->taxes;
                                    $price = $item->unit_price;
                                    $data['product_id'] = $item->product_id;
                                    $data['buy_date'] =  $PO->date_of_po;
                                    $data['number'] = $number;
                                    $data['price'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                    $assets[] = $data;
                                    $number = generateNextCodeAsset($number);
                                }
                            }
                            return $assets;
                        }
                    }
                })
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
                Tables\Columns\TextColumn::make('structure')->state(function ($record) {
                    $str = getParents($record->structure);
                    return substr($str, 1, strlen($str) - 1);
                })->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('employee')->state(function ($record) {
                    return $record->employees->last()?->assetEmployee?->employee?->fullName;
                })->badge()->url(function ($record) {
                    if ($record->employees->last()?->assetEmployee?->employee_id) {
                        return EmployeeResource::getUrl('view', ['record' => $record->employees->last()?->assetEmployee?->employee_id]);
                    }
                })->label('Employee'),

                Tables\Columns\TextColumn::make('depreciation_years')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('depreciation_amount')->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('buy_date')
                ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('garanry_date')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),


            ])
            ->filters([

                Tables\Filters\SelectFilter::make('product_id')->searchable()->options(getCompany()->products->pluck('title', 'id'))->label('Product'),


                Tables\Filters\SelectFilter::make('status')->searchable()->options(['inuse' => "Inuse", 'inStorageUsable' => "InStorageUsable", 'storageUnUsable' => "StorageUnUsable", 'outForRepair' => 'OutForRepair', 'loanedOut' => "LoanedOut"]),
                DateRangeFilter::make('buy_date'),
                DateRangeFilter::make('garanry_data'),
                DateRangeFilter::make('created_at'),
                Tables\Filters\Filter::make('employee')
                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(fn() => getCompany()->employees()->pluck('fullName', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['employee_id'] ?? null, function ($query, $employeeId) {
                            return $query->whereHas('assetEmployee', function ($subQuery) use ($employeeId) {
                                $subQuery->where('employee_id', $employeeId);
                            });
                        });
                    }),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Minimum Price')
                            ->numeric()
                            ->placeholder('Enter min price'),

                        Forms\Components\TextInput::make('max_price')
                            ->label('Maximum Price')
                            ->numeric()
                            ->placeholder('Enter max price'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_price'] ?? null, fn($query, $min) => $query->where('price', '>=', $min))
                            ->when($data['max_price'] ?? null, fn($query, $max) => $query->where('price', '<=', $max));
                    })
                    ->columns(2)
                    ->columnSpanFull(),
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
                    })->columns(2)->columnSpanFull(),


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
                        Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(50),

                        Forms\Components\TextInput::make('price')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),
                        Forms\Components\Select::make('warehouse_id')->live()->label('Warehouse')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                        SelectTree::make('structure_id')->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                            return $query->where('warehouse_id', $get('warehouse_id'));
                        })->required(),
                        DatePicker::make('garanry_date')->default(now()),
                        DatePicker::make('buy_date')->default(now()),
                        Forms\Components\Select::make('depreciation_years')
                            ->label('Depreciation Years')
                            ->options([
                                1 => '1 Year',
                                2 => '2 Years',
                                3 => '3 Years',
                                4 => '4 Years',
                                5 => '5 Years',
                            ])
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('depreciation_amount')
                            ->label('Depreciation Amount')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                            ->required()
                            ->placeholder('Enter amount'),

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
