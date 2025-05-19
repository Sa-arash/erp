<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetResource\Pages;
use App\Filament\Admin\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\Brand;
use App\Models\PurchaseOrder;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Asset';
    protected static ?string $navigationIcon = 'heroicon-s-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('purchase_order_id')->default($_GET['po']??''),

                Forms\Components\Repeater::make('assets')->schema([
                    Forms\Components\Select::make('product_id')->label('Product')->options(function () {
                        $products = getCompany()->products->where('product_type','unConsumable');
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
                    Forms\Components\TextInput::make('price')->prefix(defaultCurrency()?->symbol)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),
                    Forms\Components\Select::make('warehouse_id')->default(getCompany()->warehouse_id)->live()->label('Warehouse/Building')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                    SelectTree::make('structure_id')->default(getCompany()->structure_asset_id)->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    })->required(),
                    DatePicker::make('buy_date')->label('Purchase Date')->default(now()),
                    DatePicker::make('guarantee_date')->label('Guarantee Date')->default(now()),
                    Forms\Components\Select::make('depreciation_years')
                        ->label('Depreciation Years')
                        ->options(
                            function(){
                                $array = [];
                                for ($i=0; $i < 50; $i++) {
                                 $array[$i] = ($i+1).' Year';
                                }
                                return $array;
                            }
                        )
                        ->default(0)->searchable()->preload()
                        ->required(),
                        Forms\Components\Select::make('quality')
                        ->options(
                            [
                                'new'=>"New",
                                'used'=>"Used",
                                'refurbished'=>"Refurbished",
                            ]
                        )
                        ->default('new')->searchable()->preload()
                        ->required(),

                    Forms\Components\TextInput::make('depreciation_amount')
                        ->label('Depreciation Amount')
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->placeholder('Enter amount'),


                    Forms\Components\Hidden::make('status')->default('inStorageUsable')->required(),
                    Forms\Components\Repeater::make('attributes')->grid(3)->defaultItems(0)->addActionLabel('Add To  Attribute')->schema([
                        Forms\Components\TextInput::make('title')->required(),
                        Forms\Components\TextInput::make('value')->required(),
                    ])->columnSpanFull()->columns()
                ])->columns(4)->columnSpanFull()->default(function () {
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
                                if ($item->product->product_type === "unConsumable") {
                                    for ($i = 1; $i <= $item->quantity; $i++) {
                                        $data = [];
                                        $freights = $item->freights === null ? 0 : (float)$item->freights;
                                        $q = 1;
                                        $tax = $item->taxes === null ? 0 : (float)$item->taxes;
                                        $price = $item->unit_price;
                                        $data['product_id'] = $item->product_id;
                                        $data['buy_date'] = $PO->date_of_po;
                                        $data['number'] = $number;
                                        $data['purchase_order_id'] = $PO->id;

                                        $data['price'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                        $assets[] = $data;
                                        $number = generateNextCodeAsset($number);
                                    }
                                }
                            }
                            return $assets;
                        }
                    }
                })->defaultItems(1)->collapsed(false)->cloneable()->addActionLabel('New Asset')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('product.sku')->state(fn() => '___________')->label('SKU')->searchable()->description(function ($record) {

                    $barcode = '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($record->number, 'C39', 1, 20) . '" alt="barcode"/>';
                    $barcode .= "<p>{$record->product->sku}</p>";
                    return new HtmlString($barcode);

                })->action(function ($record) {
                    return redirect(route('pdf.barcode', ['code' => $record->id]));
                }),
                Tables\Columns\TextColumn::make('purchase_order_id')->label('PO No')->state(fn($record) => $record->purchase_order_id === null ? "---" : PurchaseOrder::find($record->purchase_order_id)->purchase_orders_number)
                    ->url(fn($record) => $record->purchase_order_id? PurchaseOrderResource::getUrl() . "?tableFilters[id][value]=" . $record->purchase_order_id:false)
                ,
                Tables\Columns\TextColumn::make('titlen')->label('Asset Name'),
                Tables\Columns\TextColumn::make('price')->label('Purchase Price')->sortable()->numeric(),

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
                Tables\Columns\TextColumn::make('quality')
                    ->sortable(),
                Tables\Columns\TextColumn::make('depreciation_years')->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('depreciation_amount')->money()->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('buy_date')->label('Purchase Date')->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('guarantee_date')->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purchase_order_id')->searchable()->options(getCompany()->purchaseOrders->pluck('purchase_orders_number', 'id'))->label('Po No'),
                Tables\Filters\SelectFilter::make('product_id')->searchable()->options(getCompany()->products->pluck('title', 'id'))->label('Product'),
                Tables\Filters\SelectFilter::make('status')->searchable()->options(['inuse' => "Inuse", 'inStorageUsable' => "InStorageUsable", 'storageUnUsable' => "StorageUnUsable", 'outForRepair' => 'OutForRepair', 'loanedOut' => "LoanedOut"]),
                DateRangeFilter::make('buy_date')->label('Purchase Date'),
                DateRangeFilter::make('guarantee_data')->label('Guarantee Data'),
                Tables\Filters\Filter::make('employee')
                    ->form([
                        Forms\Components\Select::make('employee_id')->label('Employee')->options(fn() => getCompany()->employees()->pluck('fullName', 'id'))->searchable()->preload(),
                        Forms\Components\Select::make('department_id')->label('Employee Department')->options(fn() => getCompany()->departments()->pluck('title', 'id'))->searchable()->preload(),
                        ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['employee_id'] ?? null, function ($query, $employeeId) {
                            return $query->whereHas('assetEmployee', function ($subQuery) use ($employeeId) {
                                $subQuery->where('employee_id', $employeeId);
                            });
                        })->when($data['department_id'],function ($query,$department){
                            return $query->whereHas('assetEmployee', function ($subQuery) use ($department) {
                                $subQuery->whereHas('employee', function ($query)use($department){
                                    $query->where('department_id',$department);
                                });
                            });
                        });
                    }),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')->label('Minimum Price')->numeric()->placeholder('Enter min price'),
                        Forms\Components\TextInput::make('max_price')->label('Maximum Price')->numeric()->placeholder('Enter max price'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['min_price'] ?? null, fn($query, $min) => $query->where('price', '>=', $min))->when($data['max_price'] ?? null, fn($query, $max) => $query->where('price', '<=', $max));
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
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('qr')->color('danger')->label('QR Checkout')->tooltip('Checkout')->iconSize(IconSize::Medium)->icon('heroicon-c-qr-code')->url(fn($record)=>route('pdf.qrcode',['code'=>$record->id])),
                Tables\Actions\Action::make('qrView')->color('success')->label('QR View')->tooltip('View Asset')->iconSize(IconSize::Medium)->icon('heroicon-c-qr-code')->url(fn($record)=>route('pdf.qrcode.view',['code'=>$record->id])),
                Tables\Actions\Action::make('barcode')->color('warning')->label('Barcode')->tooltip('Barcode')->iconSize(IconSize::Medium)->icon('barcode')->url(fn($record)=>route('pdf.barcode',['code'=>$record->id])),
            ])->color('warning'),
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
                        DatePicker::make('guarantee_date')->default(now()),
                        DatePicker::make('buy_date')->default(now()),

                        Forms\Components\Select::make('depreciation_years')
                            ->label('Depreciation Years')
                            ->options(
                                function(){
                                    $array = [];
                                    for ($i=0; $i < 50; $i++) {
                                     $array[$i] = ($i+1).' Year';
                                    }
                                    return $array;
                                }
                            )
                            ->default(0)->searchable()->preload()
                            ->required(),
                            Forms\Components\Select::make('quality')
                            ->options(
                                [
                                    'new'=>"New",
                                    'used'=>"Used",
                                    'refurbished'=>"Refurbished",
                                ]
                            )
                            ->default('new')->searchable()->preload()
                            ->required(),


                        Forms\Components\TextInput::make('depreciation_amount')
                            ->label('Depreciation Amount')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                            ->placeholder('Enter amount'),

                        Forms\Components\Select::make('status')->default('inStorageUsable')->options(['inuse' => "Inuse", 'inStorageUsable' => "InStorageUsable", 'storageUnUsable' => "StorageUnUsable", 'outForRepair' => "OutForRepair", 'loanedOut' => "LoanedOut"])->required()->searchable(),
                        Forms\Components\Repeater::make('attributes')->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\TextInput::make('value')->required(),
                        ])->columnSpanFull()->columns(),


                    ])->columns(3)
                ]),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Transaction')->iconSize(IconSize::Medium)->form(function ($record) {
                    return [
                        Section::make([
                            Select::make('warehouse_id')->required()->live(true)->label('Warehouse')->options(getCompany()->warehouses()->where('type', 1)->pluck('title', 'id'))->searchable()->preload(),
                            SelectTree::make('structure_id')->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Get $get) {
                                return $query->where('warehouse_id', $get('warehouse_id'));
                            })->required(),
                            Textarea::make('description')->required()->maxLength(255)->columnSpanFull(),
                        ])->columns()
                    ];
                })->action(function ($data, $record) {

                    $assetEmployee = AssetEmployee::query()->create([
                        'employee_id' => getEmployee()->id,
                        'date' => now(),
                        'approve_date' => now(),
                        'type' => 'Transaction',
                        'status' => 'Pending',
                        'description' => $data['description'],
                        'company_id' => getCompany()->id,
                    ]);
                    $assetEmployee->assetEmployeeItem()->create([
                        'asset_id' => $record->id,
                        'due_date'=>now(),
                        'warehouse_id'=>$record->warehouse_id,
                        'type'=>0,
                        'structure_id'=>$record->structure_id,
                        'company_id'=>getCompany()->id,
                    ]);
                    $assetEmployee->assetEmployeeItem()->create([
                        'asset_id' => $record->id,
                        'warehouse_id'=>$data['warehouse_id'],
                        'return_date'=>now(),
                        'type'=>1,
                        'return_approval_date'=>now(),
                        'structure_id'=>$data['structure_id'],
                        'company_id'=>getCompany()->id,

                    ]);
                    $record->update([
                        'warehouse_id'=>$data['warehouse_id'],
                        'structure_id'=>$data['structure_id'],
                    ]);

                })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                Tables\Actions\BulkAction::make('print')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->action(function ($records){
                    return redirect(route('pdf.assets',['ids'=>implode('-',$records->pluck('id')->toArray())]));
                })

            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeesRelationManager::class,
//            RelationManagers\FinanceRelationManager::class,
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
