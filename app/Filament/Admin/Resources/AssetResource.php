<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetResource\Pages;
use App\Filament\Admin\Resources\AssetResource\RelationManagers;
use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\Brand;
use App\Models\Currency;
use App\Models\Parties;
use App\Models\PurchaseOrder;
use App\Models\Structure;
use App\Models\Transaction;
use App\Models\Warehouse;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class AssetResource extends Resource
    implements HasShieldPermissions
{
    protected static ?string $model = Asset::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Asset';
    protected static ?string $navigationIcon = 'heroicon-s-inbox-stack';
    protected static ?string $recordTitleAttribute = 'number';
    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {

        $title=$record->product->title;
        $sku=$record->product->sku;
        $description=$record->description;
        $image=$record->media->where('collection_name', 'images')->first()?->original_url ??asset('img/defaultAsset.png');
        return new HtmlString("
       <div style='display: flex; align-items: center; gap: 10px;'>
            <img src='$image' alt='product image' style='width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;'>
            <div>
                <div style='font-weight: bold; font-size: 14px;'>
                    $title
                    <span style='font-weight: normal; font-size: 12px; color: #666;'>
                        — $sku
                    </span>
                </div>
                <div style='font-size: 12px; color: #555;'>Description: $description</div>
            </div>
        </div>
    ");
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'fullManager'
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Forms\Components\Hidden::make('purchase_order_id')->default($_GET['po'] ?? ''),
                    Forms\Components\Repeater::make('assets')->schema([
                        Section::make([


                            Select::make('department_id')->label('Department')->required()->columnSpan(['default' => 8, 'md' => 2, 'xl' => 2, '2xl' => 1])->live()->options(getCompany()->departments->pluck('title', 'id'))->searchable()->preload(),
                            Forms\Components\Select::make('product_id')->label('Product')->options(function (Get $get) {
                                if ($get('department_id')) {
                                    $data = [];
                                    $products = getCompany()->products->where('product_type', 'unConsumable')->where('department_id', $get('department_id'));

                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                    }
                                    return $data;
                                }
                            })->required()->searchable()->preload()->columnSpan(1),
                            TextInput::make('description'),
                            Select::make('type')->label('Asset Type')->options(getCompany()->asset_types)->createOptionForm([
                                    Forms\Components\TextInput::make('title')->required()
                                ])->createOptionUsing(function ($data) {
                                    $array = getCompany()->asset_types;
                                    if (isset($array)) {
                                        $array[$data['title']] = $data['title'];
                                    } else {
                                        $array = [$data['title'] => $data['title']];
                                    }
                                    getCompany()->update(['asset_types' => $array]);
                                    return $data['title'];
                                })->searchable()->preload()->required(),
                            Forms\Components\Select::make('warehouse_id')->default(getCompany()->warehouse_id)->live()->label('Warehouse/Building')->options(function () {
                                $data = [];
                                foreach (getCompany()->warehouses as $warehouse) {
                                    $type=$warehouse->type ? "Warehouse" : "Building";
                                    $data[$warehouse->id] = $warehouse->title . " (" . $type . ")";
                                }
                                return $data;
                            })->required()->searchable()->preload(),
                            SelectTree::make('structure_id')->default(getCompany()->structure_asset_id)->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {return $query->where('warehouse_id', $get('warehouse_id'));})->required(),
                            Forms\Components\TextInput::make('manufacturer'),
                            select::make('brand_id')->searchable()->label('Brand')->required()->options(getCompany()->brands->pluck('title', 'id'))->createOptionForm([Forms\Components\Section::make([
                                        Forms\Components\TextInput::make('title')->label('Brand Name')->required()->maxLength(255),
                                    ])])->createOptionUsing(function (array $data): int {
                                    return brand::query()->create([
                                        'title' => $data['title'],
                                        'company_id' => getCompany()->id
                                    ])->getKey();
                                }),
                            Forms\Components\TextInput::make('model')->nullable()->label('Model'),
                            Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(50),
                            Select::make('status')->searchable()->preload()->default('inStorageUsable')->options(['inuse' => 'In Use', 'inStorageUsable' => 'In Storage Usable', 'storageUnUsable' => 'Storage Unusable', 'underRepair' => 'Under Repair', 'outForRepair' => 'Out For Repair', 'loanedOut' => 'Loaned Out',]),
                            Select::make('quality')->label('Condition')->options(getCompany()->asset_qualities)->createOptionForm([
                                    Forms\Components\TextInput::make('title')->required()
                                ])->createOptionUsing(function ($data) {
                                    $array = getCompany()->asset_qualities;
                                    if (isset($array)) {
                                        $array[$data['title']] = $data['title'];
                                    } else {
                                        $array = [$data['title'] => $data['title']];
                                    }
                                    getCompany()->update(['asset_qualities' => $array]);
                                    return $data['title'];
                                })->searchable()->preload()->required(),
                            DatePicker::make('guarantee_date')->label('Due Date')->default(now()),
                            DatePicker::make('warranty_date')->label('Warranty End'),
                            TextInput::make('po_number')->label("PO Number"),
                            Textarea::make('note')->columnSpanFull(),
                        ])->columns(4),
                        DatePicker::make('buy_date')->label('Purchase Date')->default(now()),
                        Select::make('currency_id')->live()->label('Currency')->required()->searchable()->preload()->options(getCompany()->currencies->pluck('name','id'))->afterStateUpdated(function ($state, Forms\Set $set) {
                            $currency = Currency::query()->firstWhere('id', $state);
                            if ($currency) {
                                $set('exchange_rate', $currency->exchange_rate);
                            }
                        }),
                        TextInput::make('exchange_rate')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        Forms\Components\TextInput::make('price')->hintAction(Forms\Components\Actions\Action::make('Exange Rate')->fillForm(function ($state){
                            return [
                                'currency_id'=>null,
                                'exchange_rate'=>null,
                                'price'=>$state,
                            ];
                        })->form([
                            Section::make([
                                TextInput::make('price')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                Select::make('currency_id')->live()->label('Currency')->required()->searchable()->preload()->options(getCompany()->currencies->pluck('name','id'))->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $currency = Currency::query()->firstWhere('id', $state);
                                    if ($currency) {
                                        $set('exchange_rate', $currency->exchange_rate);
                                    }
                                }),
                                TextInput::make('exchange_rate')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            ])->columns(3)
                        ])->action(function ($state,Forms\Set $set,$data){
                            $finalPrice=$data['price']*$data['exchange_rate'];
                            $set('price',number_format($finalPrice,2));

                        }))->prefix(defaultCurrency()?->symbol)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),
                        Forms\Components\TextInput::make('scrap_value')->prefix(defaultCurrency()?->symbol)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Scrap Value'),
                        Forms\Components\Select::make('party_id')->label("Vendor")->searchable()->required()
                            ->options(function (Forms\Get $get) {
                                return getCompany()->parties->whereIn('type', ["vendor", 'both'])->pluck('info', 'id');
                            })->createOptionUsing(function ($data) {


                                $parentAccount = Account::query()->where('id', $data['parent_vendor'])->where('company_id', getCompany()->id)->first();
                                $check = Account::query()->where('code', $parentAccount->code . $data['account_code_vendor'])->where('company_id', getCompany()->id)->first();
                                if ($check) {
                                    Notification::make('error')->title('this Account Code Exist')->warning()->send();
                                    return;
                                }
                                $account = Account::query()->create([
                                    'currency_id' =>  $data['currency_id'],
                                    'name' =>  $data['name'],
                                    'type' => 'creditor',
                                    'code' =>  $parentAccount->code . $data['account_code_vendor'],
                                    'level' => 'detail',
                                    'parent_id' => $parentAccount->id,
                                    'group' => 'Liabilitie',
                                    'built_in' => false,
                                    'company_id' => getCompany()->id,
                                ]);
                                $data['account_vendor'] = $account->id;

                                Parties::query()->create([
                                    'name' => $data['name'],
                                    'type' => $data['type'],
                                    'address' => $data['address'],
                                    'phone' => $data['phone'],
                                    'email' => $data['email'],
                                    'account_vendor' => isset($data['account_vendor']) ? $data['account_vendor'] : null,
                                    'account_customer' => isset($data['account_customer']) ? $data['account_customer'] : null,
                                    'company_id' => getCompany()->id,
                                    'currency_id' => $data['currency_id'],
                                    'account_code_vendor' => isset($data['account_code_vendor']) ? $data['account_code_vendor'] : null,
                                    'account_code_customer' => isset($data['account_code_customer']) ? $data['account_code_customer'] : null,
                                ]);
                                Notification::make('success')->success()->title('Submitted Successfully')->color('success')->send();
                            })->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $party = Parties::query()->firstWhere('id', $state);
                                if ($get('type') === "0") {
                                    $set('from', $party?->name);
                                } else {
                                    $set('to', $party?->name);
                                }
                            })->live(true)->createOptionForm([
                                Forms\Components\Section::make([
                                    Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                                    Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                                ])->columns(3),
                                Section::make([
                                    Forms\Components\ToggleButtons::make('type')->live()->grouped()->options(['vendor' => 'Vendor', 'customer' => 'Customer', 'both' => 'Both'])->inline()->required(),
                                    Select::make('currency_id')->live()->model(Parties::class)->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
                                        \Filament\Forms\Components\Section::make([
                                            TextInput::make('name')->required()->maxLength(255),
                                            TextInput::make('symbol')->required()->maxLength(255),
                                            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                        ])->columns(3)
                                    ])->createOptionUsing(function ($data) {
                                        $data['company_id'] = getCompany()->id;
                                        Notification::make('success')->title('success')->success()->send();
                                        return Currency::query()->create($data)->getKey();
                                    })->editOptionForm([
                                        \Filament\Forms\Components\Section::make([
                                            TextInput::make('name')->required()->maxLength(255),
                                            TextInput::make('symbol')->required()->maxLength(255),
                                            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                        ])->columns(3)
                                    ]),
                                    SelectTree::make('parent_vendor')->visible(function (Forms\Get $get) {

                                        if ($get('type') == "both") {
                                            if ($get("account_vendor") === null) {
                                                return true;
                                            }
                                        } elseif ($get('type') == "vendor") {
                                            if ($get("account_vendor") === null) {
                                                return true;
                                            }
                                        } else {
                                            return false;
                                        }
                                    })->disabledOptions(function () {
                                        return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                                    })->hidden(fn($operation) => (bool)$operation === "edit")->default(getCompany()?->vendor_account)->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Vendor Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id)),

                                    SelectTree::make('parent_customer')->visible(function (Forms\Get $get) {
                                        if ($get('type') == "both") {
                                            if ($get("account_customer") === null) {
                                                return true;
                                            }
                                        } elseif ($get('type') == "customer") {
                                            if ($get("account_customer") === null) {
                                                return true;
                                            }
                                        } else {
                                            return false;
                                        }
                                    })->default(getCompany()?->customer_account)->disabledOptions(function ($state, SelectTree $component) {
                                        return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                                    })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Customer Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Assets")->where('company_id', getCompany()->id)),
                                    Forms\Components\TextInput::make('account_code_vendor')
                                        ->prefix(fn(Get $get) => Account::find($get('parent_vendor'))?->code)
                                        ->default(function () {
                                            if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()) {
                                                return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()->account_code_vendor);
                                            } else {
                                                return "001";
                                            }
                                        })->unique('accounts', 'code', ignoreRecord: true)->visible(function (Forms\Get $get) {

                                            if ($get('type') == "both") {
                                                if ($get("account_vendor") === null) {
                                                    return true;
                                                }
                                            } elseif ($get('type') == "vendor") {
                                                if ($get("account_vendor") === null) {
                                                    return true;
                                                }
                                            } else {
                                                return false;
                                            }
                                        })->required()->maxLength(255),
                                    Forms\Components\TextInput::make('account_code_customer')->unique('accounts', 'code', ignoreRecord: true)
                                        ->prefix(fn(Get $get) => Account::find($get('parent_customer'))?->code)
                                        ->default(function () {
                                            if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'customer')->latest()->first()) {
                                                return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'customer')->latest()->first()->account_code_customer);
                                            } else {
                                                return "001";
                                            }
                                        })->visible(function (Forms\Get $get) {
                                            if ($get('type') === "both") {
                                                if ($get("account_customer") === null) {
                                                    return true;
                                                }
                                            } elseif ($get('type') === "customer") {
                                                if ($get("account_customer") === null) {
                                                    return true;
                                                }
                                            } else {
                                                return false;
                                            }
                                        })->required()->tel()->maxLength(255),
                                ])->columns()
                            ]),


                        Select::make('depreciation_years')
                            ->label('Recovery Period')
                            ->options(getCompany()->asset_depreciation_years)
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')->required()
                            ])->createOptionUsing(function ($data) {
                                $array = getCompany()->asset_depreciation_years;
                                if (isset($array)) {
                                    $array[$data['title']] = $data['title'];
                                } else {
                                    $array = [$data['title'] => $data['title']];
                                }
                                getCompany()->update(['asset_depreciation_years' => $array]);
                                return $data['title'];
                            })->searchable()->preload(),

                        Forms\Components\TextInput::make('depreciation_amount')
                            ->label('Market Value')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                            ->placeholder('Enter amount'),
                        Forms\Components\TextInput::make('number')->default(function () {
                            $asset = Asset::query()->where('company_id', getCompany()->id)->latest()->first();
                            if ($asset) {
                                return generateNextCodeAsset($asset->number);
                            } else {
                                return "0001";
                            }
                        })->required()->numeric()->label('Asset Number')->readOnly()->maxLength(50),
                        Forms\Components\Repeater::make('attributes')->grid(3)->defaultItems(0)->addActionLabel('Add To  Attribute')->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\TextInput::make('value')->required(),
                        ])->columnSpanFull()->columns(),
                        MediaManagerInput::make('images')->orderable(false)->folderTitleFieldName("product_id")->image(true)
                            ->disk('public')
                            ->schema([])->maxItems(1)->columnSpanFull(),
                    ])->columns(4)->columnSpanFull()->default(function () {

                        if (isset($_GET['po'])) {
                            $asset = Asset::query()->where('company_id', getCompany()->id)->latest()->first();
                            if ($asset) {
                                $number = generateNextCodeAsset($asset->number);
                            } else {
                                $number = "0001";
                            }

                            $PO = PurchaseOrder::query()->with(['items','items.product'])->firstWhere('id', $_GET['po']);
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
                                            $data['po_number'] = $PO->purchase_orders_number;
                                            $data['department_id'] = $item->product->department_id;
                                            $data['buy_date'] = $PO->date_of_po;
                                            $data['number'] = $number;
                                            $data['purchase_order_id'] = $PO->id;
                                            $data['party_id']=$PO->party_id;
                                            $data['price'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                            $assets[] = $data;
                                            $number = generateNextCodeAsset($number);
                                        }
                                    }
                                }
                                return $assets;
                            }
                        } else {
                            $asset = Asset::query()->where('company_id', getCompany()->id)->latest()->first();
                            if ($asset) {
                                $code = generateNextCodeAsset($asset->number);
                            } else {
                                $code = "0001";
                            }
                            return [
                                [
                                    'product_id' => null,
                                    'buy_date' => null,
                                    'number' => $code,
                                    'price' => null,
                                    'purchase_order_id' => null,
                                ]
                            ];
                        }
                    })->collapsed(false)->cloneable()->addActionLabel('New Asset')
                ]
            );
    }

    public static function table(Table $table): Table
    {
        return $table->query(function () {
            if (\auth()->user()->can('fullManager_asset')) {
                return Asset::query()->where('company_id', getCompany()->id);
            } else {
                return Asset::query()->where('company_id', getCompany()->id)->where('manager_id', getEmployee()->id);
            }
        })
            ->defaultSort('id', 'desc')->headerActions([
                Tables\Actions\Action::make('auditCheckList')
                    ->label('Print Audit Check List')->form([
                        Select::make('by')->required()->default('warehouse_id')->label('Asset By')->options(['warehouse_id'=>'Location','brand_id'=>'Brand','type'=>'Type','po_number'=>'PO','party_id'=>'Vendor'])->searchable()->preload()

                    ])->color('danger')->visible(fn()=>\auth()->user()->can('fullManager_asset'))
                    ->action(function ($data) {

                        if ($data['by']) {
                            return redirect()->route('pdf.audit-checklist', [
                                'type' => $data['by'],
                                'company' => getCompany()->id
                            ]);
                        }
                    })->openUrlInNewTab(),
                Tables\Actions\Action::make('print')
                    ->label('Print Report')->form([
                        Select::make('warehouses')->multiple()->options(function () {
                            $data = [];

                            if (\auth()->user()->can('fullManager_asset')) {
                                $warehouses = Warehouse::query()->where('company_id', getCompany()->id)->get();
                            } else {
                                $warehouses = Warehouse::query()->where('type', 1)->where('company_id', getCompany()->id)->where('employee_id', getEmployee()->id)->get();
                            }
                            foreach ($warehouses as $warehouse) {
                                $type = $warehouse->type ? "Warehouse" : "Building";
                                $data[$warehouse->id] = $warehouse->title . " (" . $type . ")";
                            }
                            return $data;
                        })->required()
                    ])->color('warning')
                    ->action(function ($data) {

                        if ($data['warehouses']) {
                            return redirect()->route('pdf.assets-balance', [
                                'ids' => implode('-', $data['warehouses']),
                                'company' => getCompany()->id
                            ]);
                        }
                    })->openUrlInNewTab(),
                ExportAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . " Assets");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Assets")->withColumns([
                            Column::make('product.sku')->heading("product sku"),
                            Column::make('purchase_order_id')->heading('PO No')->formatStateUsing(fn($record) => $record->purchase_order_id === null ? "---" : PurchaseOrder::find($record->purchase_order_id)->purchase_orders_number),
                            Column::make('description')->heading('Asset Description'),
                            Column::make('price')->heading('Purchase Price'),

                            Column::make('warehouse.title')->heading('Warehouse/Building'),
                            Column::make('structure')->formatStateUsing(function ($record) {
                                $str = getParents($record->structure);
                                return substr($str, 1, strlen($str) - 1);
                            })->heading('Location'),
                            Column::make('employee')->formatStateUsing(function ($record) {
                                if ($record->employees?->last()) {
                                    $data = $record->employees?->last()?->assetEmployee;
                                    if ($data->type === 'Assigned')
                                        return $data?->employee?->fullName;
                                }
                            })->heading('Employee'),
                            Column::make('manufacturer')->heading('Manufacturer'),
                            Column::make('quality')->heading('Condition'),
                            Column::make('depreciation_years'),
                            Column::make('depreciation_amount'),
                            Column::make('buy_date')->heading('Purchase Date'),
                            Column::make('guarantee_date'),
                            Column::make('status'),
                            Column::make('product.department.title')->heading('Product Department'),
                        ]),
                    ])->label('Export')->color('purple')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('media.original_url')->state(function ($record) {
                    return $record->media->where('collection_name', 'images')->first()?->original_url;
                })->disk('public')
                    ->defaultImageUrl(fn($record) => asset('img/defaultAsset.png'))
                    ->alignLeft()->label('Asset Picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('number')->state(fn() => '___________')->label('Barcode')->searchable()->description(function ($record) {

                    $barcode = '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($record->number, 'C39', 1, 20) . '" alt="barcode"/>';
                    $barcode .= "<p style='text-align: center'>{$record->number}</p>";
                    return new HtmlString($barcode);
                })->action(function ($record) {
                    return redirect(route('pdf.barcode', ['code' => $record->id]));
                }),
                Tables\Columns\TextColumn::make('purchase_order_id')->label('PO No')->state(fn($record) => $record->purchase_order_id === null ? "---" : PurchaseOrder::find($record->purchase_order_id)->purchase_orders_number)
                    ->url(fn($record) => $record->purchase_order_id ? PurchaseOrderResource::getUrl() . "?tableFilters[id][value]=" . $record->purchase_order_id : false),
                Tables\Columns\TextColumn::make('titlen')->label('Asset Description'),
                Tables\Columns\TextColumn::make('brand.title'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('employee')->state(function ($record){return $record->check_out_to ? $record?->checkOutTo?->fullName:$record?->person?->name;})->badge()->label('Custodian'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Date Update'),
                Tables\Columns\TextColumn::make('Due Date')->state(function ($record) {
                    if ($record->employees?->last()) {
                        $data = $record->employees?->last();

                        if ($data)
                            return $data?->due_date;
                    }
                })->dateTime()->label('Due Date'),

                Tables\Columns\TextColumn::make('serial_number'),
                Tables\Columns\TextColumn::make('status')->state(fn($record) => match ($record->status) {
                    'inuse' => "In Use",
                    'inStorageUsable' => "In Storage",
                    'loanedOut' => "Loaned Out",
                    'outForRepair' => 'Out For Repair',
                    'StorageUnUsable' => " Scrap"
                })->badge(),

                Tables\Columns\TextColumn::make('price')->label('Purchase Price')->sortable()->numeric(),

                Tables\Columns\TextColumn::make('warehouse.title')->label('Warehouse/Building')->sortable(),
                Tables\Columns\TextColumn::make('structure')->state(function ($record) {
                    $str = getParents($record->structure);
                    return substr($str, 1, strlen($str) - 1);
                })->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('quality')->label('Condition')->sortable(),
                Tables\Columns\TextColumn::make('depreciation_years')->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('depreciation_amount')->money()->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('buy_date')->label('Purchase Date')->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('guarantee_date')->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('manufacturer'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purchase_order_id')->searchable()->options(getCompany()->purchaseOrders->pluck('purchase_orders_number', 'id'))->label('PO No'),
                Tables\Filters\SelectFilter::make('product_id')->searchable()->options(getCompany()->products->where('product_type','unConsumable')->pluck('title', 'id'))->label('Product'),
                Tables\Filters\SelectFilter::make('status')->searchable()->options(['inuse' => "In Use", 'inStorageUsable' => "In Storage",  'loanedOut' => "Loaned Out", 'outForRepair' => 'Out For Repair', 'StorageUnUsable' => " Scrap"]),
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
                        })->when($data['department_id'], function ($query, $department) {
                            return $query->whereHas('assetEmployee', function ($subQuery) use ($department) {
                                $subQuery->whereHas('employee', function ($query) use ($department) {
                                    $query->where('department_id', $department);
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
                        Forms\Components\Select::make('warehouse_id')->label('Warehouse')->options(function () {
                            $data = [];
                            foreach (getCompany()->warehouses as $warehouse) {
                                $type=$warehouse->type ? "Warehouse" : "Building";
                                $data[$warehouse->id] = $warehouse->title . " (" . $type . ")";
                            }
                            return $data;
                        })->searchable()->preload()->afterStateUpdated(function (Forms\Set $set){
                                $set('structure_id',null);
                        }),
                        SelectTree::make('structure_id')->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                            return $query->where('warehouse_id', $get('warehouse_id'));
                        })->required()
                    ])
                    ->query(function (Builder $query, array $data) {

                        return $query->when($data['warehouse_id'], function ($query, $data) {
                            return $query->where('warehouse_id', $data);
                        })->when($data['structure_id'], function ($query, $data) {
                            return $query->where('structure_id', $data);
                        });
                    })->columns(2)->columnSpanFull(),


            ], getModelFilter())
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('qrView')->color('success')->label('QR View')->tooltip('View Asset')->iconSize(IconSize::Medium)->icon('heroicon-c-qr-code')->url(fn($record) => route('pdf.qrcode.view', ['code' => $record->id])),
                    Tables\Actions\Action::make('barcode')->color('warning')->label('Barcode')->tooltip('Barcode')->iconSize(IconSize::Medium)->icon('barcode')->url(fn($record) => route('pdf.barcode', ['code' => $record->number])),
                    ])->color('warning'),
                    Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                        ->url(fn($record) => route('pdf.asset', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                ->form([
                        Section::make([


                            Select::make('department_id')->label('Department')->required()->columnSpan(['default' => 8, 'md' => 2, 'xl' => 2, '2xl' => 1])->live()->options(getCompany()->departments->pluck('title', 'id'))->searchable()->preload(),
                            Forms\Components\Select::make('product_id')->label('Product')->options(function (Get $get) {

                                if ($get('department_id')) {
                                    $data = [];
                                    $products = getCompany()->products->where('product_type', 'unConsumable')->where('department_id', $get('department_id'));

                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                    }
                                    return $data;
                                }
                            })->required()->searchable()->preload()->columnSpan(1),
                            TextInput::make('description'),
                            Select::make('type')
                                ->label('Asset Type')
                                ->options(getCompany()->asset_types)
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('title')->required()
                                ])->createOptionUsing(function ($data) {
                                    $array = getCompany()->asset_types;
                                    if (isset($array)) {
                                        $array[$data['title']] = $data['title'];
                                    } else {
                                        $array = [$data['title'] => $data['title']];
                                    }
                                    getCompany()->update(['asset_types' => $array]);
                                    return $data['title'];
                                })->searchable()->preload()->required(),
                            Forms\Components\Select::make('warehouse_id')->default(getCompany()->warehouse_id)->live()->label('Warehouse/Building')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                            SelectTree::make('structure_id')->default(getCompany()->structure_asset_id)->searchable()->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                                return $query->where('warehouse_id', $get('warehouse_id'));
                            })->required(),

                            Forms\Components\TextInput::make('manufacturer'),
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


                            Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(50),
                            Select::make('status')
                                ->searchable()->preload()
                                ->default('inStorageUsable')
                                ->options(['inuse' => 'In Use', 'inStorageUsable' => 'In Storage Usable', 'storageUnUsable' => 'Storage Unusable', 'underRepair' => 'Under Repair', 'outForRepair' => 'Out For Repair', 'loanedOut' => 'Loaned Out',]),

                                Select::make('quality')
                                ->label('Condition')
                                ->options(getCompany()->asset_qualities)
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('title')->required()
                                ])->createOptionUsing(function ($data) {
                                    $array = getCompany()->asset_qualities;
                                    if (isset($array)) {
                                        $array[$data['title']] = $data['title'];
                                    } else {
                                        $array = [$data['title'] => $data['title']];
                                    }
                                    getCompany()->update(['asset_qualities' => $array]);
                                    return $data['title'];
                                })->searchable()->preload()->required(),

                            Forms\Components\Select::make('check_out_to')
                                ->options(function () {
                                    $data = [];
                                    $employees = getCompany()->employees;
                                    foreach ($employees as $employee) {
                                        $data[$employee->id] = $employee->fullName;
                                    }
                                    return $data;
                                })->searchable()->preload()
                                ->required(),
                            DatePicker::make('guarantee_date')->label('Due Date')->default(now()),
                            DatePicker::make('warranty_date')->label('Warranty End'),
                            TextInput::make('po_number')->label("PO Number"),
                            Textarea::make('note')->columnSpanFull(),



                        DatePicker::make('buy_date')->label('Purchase Date')->default(now()),
                        Forms\Components\TextInput::make('price')->prefix(defaultCurrency()?->symbol)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric()->label('Purchase Price'),

                        Forms\Components\TextInput::make('scrap_value')->prefix(defaultCurrency()?->symbol)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->numeric()->label('Scrap Value'),

                        Forms\Components\Select::make('party_id')->label("Vendor")->searchable()->required()
                            ->options(function (Forms\Get $get) {
                                return getCompany()->parties->whereIn('type', ["vendor", 'both'])->pluck('info', 'id');
                            })->createOptionUsing(function ($data) {


                                $parentAccount = Account::query()->where('id', $data['parent_vendor'])->where('company_id', getCompany()->id)->first();
                                $check = Account::query()->where('code', $parentAccount->code . $data['account_code_vendor'])->where('company_id', getCompany()->id)->first();
                                if ($check) {
                                    Notification::make('error')->title('this Account Code Exist')->warning()->send();
                                    return;
                                }
                                $account = Account::query()->create([
                                    'currency_id' =>  $data['currency_id'],
                                    'name' =>  $data['name'],
                                    'type' => 'creditor',
                                    'code' =>  $parentAccount->code . $data['account_code_vendor'],
                                    'level' => 'detail',
                                    'parent_id' => $parentAccount->id,
                                    'group' => 'Liabilitie',
                                    'built_in' => false,
                                    'company_id' => getCompany()->id,
                                ]);
                                $data['account_vendor'] = $account->id;

                                Parties::query()->create([
                                    'name' => $data['name'],
                                    'type' => $data['type'],
                                    'address' => $data['address'],
                                    'phone' => $data['phone'],
                                    'email' => $data['email'],
                                    'account_vendor' => isset($data['account_vendor']) ? $data['account_vendor'] : null,
                                    'account_customer' => isset($data['account_customer']) ? $data['account_customer'] : null,
                                    'company_id' => getCompany()->id,
                                    'currency_id' => $data['currency_id'],
                                    'account_code_vendor' => isset($data['account_code_vendor']) ? $data['account_code_vendor'] : null,
                                    'account_code_customer' => isset($data['account_code_customer']) ? $data['account_code_customer'] : null,
                                ]);
                                Notification::make('success')->success()->title('Submitted Successfully')->color('success')->send();
                            })->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $party = Parties::query()->firstWhere('id', $state);
                                if ($get('type') === "0") {
                                    $set('from', $party?->name);
                                } else {
                                    $set('to', $party?->name);
                                }
                            })->live(true)->createOptionForm([
                                Forms\Components\Section::make([
                                    Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                                    Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                                ])->columns(3),
                                Section::make([
                                    Forms\Components\ToggleButtons::make('type')->live()->grouped()->options(['vendor' => 'Vendor', 'customer' => 'Customer', 'both' => 'Both'])->inline()->required(),
                                    Select::make('currency_id')->live()->model(Parties::class)->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
                                        \Filament\Forms\Components\Section::make([
                                            TextInput::make('name')->required()->maxLength(255),
                                            TextInput::make('symbol')->required()->maxLength(255),
                                            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                        ])->columns(3)
                                    ])->createOptionUsing(function ($data) {
                                        $data['company_id'] = getCompany()->id;
                                        Notification::make('success')->title('success')->success()->send();
                                        return Currency::query()->create($data)->getKey();
                                    })->editOptionForm([
                                        \Filament\Forms\Components\Section::make([
                                            TextInput::make('name')->required()->maxLength(255),
                                            TextInput::make('symbol')->required()->maxLength(255),
                                            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                        ])->columns(3)
                                    ]),

                                    SelectTree::make('parent_vendor')->visible(function (Forms\Get $get) {

                                        if ($get('type') == "both") {
                                            if ($get("account_vendor") === null) {
                                                return true;
                                            }
                                        } elseif ($get('type') == "vendor") {
                                            if ($get("account_vendor") === null) {
                                                return true;
                                            }
                                        } else {
                                            return false;
                                        }
                                    })->disabledOptions(function () {
                                        return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                                    })->hidden(fn($operation) => (bool)$operation === "edit")->default(getCompany()?->vendor_account)->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Vendor Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id)),
                                    SelectTree::make('parent_customer')->visible(function (Forms\Get $get) {
                                        if ($get('type') == "both") {
                                            if ($get("account_customer") === null) {
                                                return true;
                                            }
                                        } elseif ($get('type') == "customer") {
                                            if ($get("account_customer") === null) {
                                                return true;
                                            }
                                        } else {
                                            return false;
                                        }
                                    })->default(getCompany()?->customer_account)->disabledOptions(function ($state, SelectTree $component) {
                                        return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                                    })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Customer Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Assets")->where('company_id', getCompany()->id)),
                                    Forms\Components\TextInput::make('account_code_vendor')
                                        ->prefix(fn(Get $get) => Account::find($get('parent_vendor'))?->code)
                                        ->default(function () {
                                            if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()) {
                                                return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()->account_code_vendor);
                                            } else {
                                                return "001";
                                            }
                                        })->unique('accounts', 'code', ignoreRecord: true)->visible(function (Forms\Get $get) {

                                            if ($get('type') == "both") {
                                                if ($get("account_vendor") === null) {
                                                    return true;
                                                }
                                            } elseif ($get('type') == "vendor") {
                                                if ($get("account_vendor") === null) {
                                                    return true;
                                                }
                                            } else {
                                                return false;
                                            }
                                        })->required()->maxLength(255),
                                    Forms\Components\TextInput::make('account_code_customer')->unique('accounts', 'code', ignoreRecord: true)
                                        ->prefix(fn(Get $get) => Account::find($get('parent_customer'))?->code)
                                        ->default(function () {
                                            if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'customer')->latest()->first()) {
                                                return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'customer')->latest()->first()->account_code_customer);
                                            } else {
                                                return "001";
                                            }
                                        })->visible(function (Forms\Get $get) {
                                            if ($get('type') === "both") {
                                                if ($get("account_customer") === null) {
                                                    return true;
                                                }
                                            } elseif ($get('type') === "customer") {
                                                if ($get("account_customer") === null) {
                                                    return true;
                                                }
                                            } else {
                                                return false;
                                            }
                                        })->required()->tel()->maxLength(255),
                                ])->columns()
                            ]),
                        Select::make('depreciation_years')->label('Recovery Period')->options(getCompany()->asset_depreciation_years)->createOptionForm([
                                Forms\Components\TextInput::make('title')->required()
                            ])->createOptionUsing(function ($data) {
                                $array = getCompany()->asset_depreciation_years;
                                if (isset($array)) {
                                    $array[$data['title']] = $data['title'];
                                } else {
                                    $array = [$data['title'] => $data['title']];
                                }
                                getCompany()->update(['asset_depreciation_years' => $array]);
                                return $data['title'];
                            })->searchable()->preload(),
                        Forms\Components\TextInput::make('depreciation_amount')->label('Market Value')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->placeholder('Enter amount'),
                        Forms\Components\TextInput::make('number')->default(function () {
                            $asset = Asset::query()->where('company_id', getCompany()->id)->latest()->first();
                            if ($asset) {
                                return generateNextCodeAsset($asset->number);
                            } else {
                                return "0001";
                            }
                        })->required()->numeric()->label('Asset Number')->readOnly()->maxLength(50),

                        // Forms\Components\Hidden::make('status')->default('inStorageUsable')->required(),
                        Forms\Components\Repeater::make('attributes')->grid(3)->defaultItems(0)->addActionLabel('Add To  Attribute')->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\TextInput::make('value')->required(),
                        ])->columnSpanFull()->columns(),
                        MediaManagerInput::make('images')->orderable(false)->folderTitleFieldName("product_id")->image(true)
                            ->disk('public')
                            ->schema([])->maxItems(1)->columnSpanFull(),
                            ])->columns(4),
                    ]

                )->modalWidth(MaxWidth::Full),
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
                        'due_date' => now(),
                        'warehouse_id' => $record->warehouse_id,
                        'type' => 0,
                        'structure_id' => $record->structure_id,
                        'company_id' => getCompany()->id,
                    ]);
                    $assetEmployee->assetEmployeeItem()->create([
                        'asset_id' => $record->id,
                        'warehouse_id' => $data['warehouse_id'],
                        'return_date' => now(),
                        'type' => 1,
                        'return_approval_date' => now(),
                        'structure_id' => $data['structure_id'],
                        'company_id' => getCompany()->id,

                    ]);
                    $record->update([
                        'warehouse_id' => $data['warehouse_id'],
                        'structure_id' => $data['structure_id'],
                    ]);
                })
            ])
            ->bulkActions([
                ExportBulkAction::make()
                ->after(function () {
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . "Assets");
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Assets")->withColumns([
                        Column::make('product.sku')->heading("Product sku"),
                        Column::make('number')->heading("Asset Number"),
                        Column::make('purchase_order_id')->heading('PO No')->formatStateUsing(fn($record) => $record->purchase_order_id === null ? "---" : PurchaseOrder::find($record->purchase_order_id)->purchase_orders_number),
                        Column::make('description')->heading('Asset Description'),
                        Column::make('price')->heading('Purchase Price'),

                        Column::make('warehouse.title')->heading('Warehouse/Building'),
                        Column::make('structure')->formatStateUsing(function ($record) {
                            $str = getParents($record->structure);
                            return substr($str, 1, strlen($str) - 1);
                        })->heading('Location'),
                        Column::make('employee')->formatStateUsing(function ($record) {
                            if ($record->employees?->last()) {
                                $data = $record->employees?->last()?->assetEmployee;
                                if ($data->type === 'Assigned')
                                    return $data?->employee?->fullName;
                            }
                        })->heading('Employee'),
                        Column::make('manufacturer')->heading('Manufacturer'),
                        Column::make('quality')->heading('Condition'),
                        Column::make('depreciation_years'),
                        Column::make('depreciation_amount'),
                        Column::make('buy_date')->heading('Purchase Date'),
                        Column::make('guarantee_date'),
                        Column::make('status'),
                        Column::make('product.department.title')->heading('Product Department'),
                    ]),
                ])->label('Export')->color('purple'),

                Tables\Actions\BulkAction::make('print')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->action(function ($records,$data) {
                    return redirect(route('pdf.assets', ['ids' => implode('-', $records->pluck('id')->toArray()), 'company' => getCompany()->id,'type'=>$data['by']]));
                })->form([
                    Select::make('by')->required()->default('warehouse_id')->label('Asset By')->options(['warehouse_id'=>'Location','brand_id'=>'Brand','type'=>'Type','po_number'=>'PO','party_id'=>'Vendor'])->searchable()->preload()
                ]),
                Tables\Actions\BulkAction::make('printBarcode')->label('Print Barcode ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->action(function ($records) {
                    return redirect(route('pdf.barcodes', ['codes' => implode('-', $records->pluck('number')->toArray())]));
                })->color('success'),

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
