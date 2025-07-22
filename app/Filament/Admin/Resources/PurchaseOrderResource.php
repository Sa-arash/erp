<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Currency;
use App\Models\Inventory;
use App\Models\Parties;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Stock;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PurchaseOrderResource extends Resource
implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
//            'Head Logistic',
//            'Stock',
//            'Finance',
            'invoice',
        ];
    }



    public static function canCreate(): bool
    {
        return getPeriod() != null;
    }


    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationGroup = 'Logistic Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['employee', 'employee.position', 'items', 'approvals', 'approvals.employee']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Wizard::make([
                //     Wizard\Step::make('Order')
                // ->schema([
                Section::make('Order Info')->schema([

                    Forms\Components\Select::make('purchase_request_id')->prefix('ATGT/UNC/')
                                    ->default(function (Request $request) {
                                        return $request?->prno;
                                    })
                                    ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                        if ($state) {
                                            $record = PurchaseRequest::query()->with(['bid','bid.quotation','bid.quotation.quotationItems','items'])->firstWhere('id', $state);
                                            $set('purchase_orders_number', $record->purchase_number.'1');
                                            if ($record->bid) {
                                                $data = [];
                                                $i=1;

                                                foreach ($record->bid->quotation?->quotationItems->toArray() as $item) {
                                                    $prItem = PurchaseRequestItem::query()->firstWhere('id', $item['purchase_request_item_id']);
                                                    $item['quantity'] = $prItem->quantity;
                                                    $item['unit_id'] = $prItem->unit_id;
                                                    $item['description'] = $prItem->description;
                                                    $item['product_id'] = $prItem->product_id;
                                                    $item['project_id'] = $prItem->project_id;
                                                    $q = $prItem->quantity;
                                                    $item['unit_price'] = number_format($item['unit_rate']);
                                                    $price = $item['unit_rate'];
                                                    $tax = $item['taxes'];
                                                    $item['row_number'] = $i++;

                                                    $freights = $item['freights'];

                                                    $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                                    $data[] = $item;
                                                }
                                                $set('RequestedItems', $data);
                                                $set('vendor_id', $record->bid->quotation->party_id);
                                                $set('currency_id', $record->bid->quotation->currency_id);
                                            } else {

                                                $data = $record->items->where('status', 'approve')->toArray();
                                                if ($data===null){
                                                    $data=[];
                                                }
                                                $i=1;
                                                foreach($data as &$item) {
                                                    $item['taxes'] = 0;
                                                    $item['row_number'] = $i++;
                                                    $item['freights'] = 0;
                                                    $item['unit_price'] = number_format($item['estimated_unit_cost']);

                                                }

                                                // dd($data);
                                                $set('RequestedItems', $data);
                                                // dd($get('RequestedItems'),$record->items->where('status', 'approve')->toArray());
                                            }
                                        }
                                    })
                                    ->live()
                                    ->label('PR No')
                                    ->searchable()
                                    ->preload()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $record = PurchaseRequest::query()->with(['bid','bid.quotation','bid.quotation.quotationItems','items'])->firstWhere('id', $state);
                                            $set('purchase_orders_number', $record->purchase_number);
                                            if ($record->bid) {
                                                $data = [];
                                                foreach ($record->bid->quotation?->quotationItems->toArray() as $item) {
                                                    $prItem = PurchaseRequestItem::query()->firstWhere('id', $item['purchase_request_item_id']);
                                                    $item['quantity'] = $prItem->quantity;
                                                    $item['unit_id'] = $prItem->unit_id;
                                                    $item['description'] = $prItem->description;
                                                    $item['product_id'] = $prItem->product_id;
                                                    $item['project_id'] = $prItem->project_id;
                                                    $q = $prItem->quantity;
                                                    $item['unit_price'] = number_format($item['unit_rate']);
                                                    $price = $item['unit_rate'];
                                                    $tax = $item['taxes'];
                                                    $freights = $item['freights'];
                                                    $item['vendor_id']=$record->bid->quotation->party_id;
                                                    $item['currency_id']=$record->bid->quotation->party->currency_id;

                                                    $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                                    $data[] = $item;
                                                }
                                                $set('RequestedItems', $data);
                                                $set('currency_id', $record->bid->quotation->currency_id);
                                                $set('exchange_rate', $record->bid->quotation->currency->exchange_rate);
                                            } else {
                                                $data = [];
                                                $i=1;
                                                foreach ($record->items->where('status', 'approve')->toArray() as $item) {
                                                    $item['row_number'] = $i++;
                                                    $item['taxes'] = 0;
                                                    $item['freights'] = 0;
                                                    $item['vendor_id'] = null;
                                                    $item['exchange_rate'] = null;
                                                    $item['currency_id'] = null;
                                                    $item['unit_price'] = number_format($item['estimated_unit_cost']);
                                                    $item['total'] = number_format($item['estimated_unit_cost']*$item['quantity']);
                                                    $data[] = $item;
                                                }
                                                $set('RequestedItems', $data);
                                            }
                                        }
                                    })
                                    ->options(function ($operation,$record){
                                        if ($operation==="edit"){
                                            $data=[];
                                            foreach (getCompany()->purchaseRequests()->where('id',$record->purchase_request_id)->get() as $item){
                                                $data[$item->id]=  $item->purchase_number.'('.$item->employee?->fullName.')';
                                            }
                                            return $data;
                                        }else{
                                            $data=[];
                                            foreach (getCompany()->purchaseRequests()->where('status','Approval')->whereHas('purchaseOrder',function (){},'!=')->orderBy('id', 'desc')->get() as $item){
                                                $data[$item->id]=  $item->purchase_number.'('.$item->employee?->fullName.')';
                                            }
                                            return $data;
                                        }

                                    }),

                                Forms\Components\DateTimePicker::make('date_of_po')->default(now())
                                    ->label('Date of PO')->afterOrEqual(now()->startOfHour())
                                    ->required(),

                                Forms\Components\Select::make('prepared_by')->live()
                                    ->searchable()
                                    ->preload()
                                    ->label('Processed By')
                                    ->options(fn($operation,$record)=> $operation==="edit"? getCompany()->employees->where('id',$record->prepared_by)->pluck('fullName', 'id'):getCompany()->employees->where('id',getEmployee()->id)->pluck('fullName', 'id'))
                                    ->default(fn() => getEmployee()->id)->required(),

                                Forms\Components\TextInput::make('purchase_orders_number')->label('PO No')->prefix('ATGT/UNC/')->readOnly()
                                    ->required()->default(function (Request $request){
                                        $PR=PurchaseRequest::query()->firstWhere('id',$request?->prno);
                                        if ($PR){
                                            return $PR->purchase_number;
                                        }
                                    })
                                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                                        return $rule->where('company_id', getCompany()->id);
                                    })->maxLength(50),
                    Forms\Components\TextInput::make('location_of_delivery')->maxLength(255),
                    Forms\Components\DatePicker::make('date_of_delivery')
                        ->default(now()),
                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),
                    Forms\Components\Hidden::make('currency'),
//                                Repeater::make('RequestedItems')->reorderableWithDragAndDrop(false)->defaultItems(1)->required()
//                                    ->default(function (Request $request, Set $set) {
//                                        $record = (PurchaseRequest::query()->with('bid')->firstWhere('id', $request->prno));
//                                        if ($record?->bid) {
//                                            $data = [];
//                                            foreach ($record->bid->quotation?->quotationItems->toArray() as $item) {
//                                                $prItem = PurchaseRequestItem::query()->firstWhere('id', $item['purchase_request_item_id']);
//                                                $item['quantity'] = $prItem->quantity;
//                                                $item['unit_id'] = $prItem->unit_id;
//                                                $item['description'] = $prItem->description;
//                                                $item['product_id'] = $prItem->product_id;
//                                                $item['project_id'] = $prItem->project_id;
//                                                $item['vendor_id'] = $record->bid->quotation->party_id;
//                                                $item['currency_id'] = $record->bid->quotation->currecny_id;
//                                                $item['exchange_rate'] = $record->bid->quotation->currency->exchange_rate;
//                                                $q = $prItem->quantity;
//                                                $item['unit_price'] = number_format($item['unit_rate']);
//                                                $price = $item['unit_rate'];
//                                                $tax = $item['taxes'];
//                                                $freights = $item['freights'];
//
//                                                $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
//                                                $data[] = $item;
//                                            }
//
//                                            return  $data;
//                                        } else {
//
//                                            $data=$record?->items->where('status', 'approve')->toArray();
//                                            if ($data===null){
//                                                $data=[];
//                                            }
//                                            $i=1;
//                                            foreach ($data as  &$item){
//                                                $item['row_number']=$i++;
//                                                $item['unit_price']=number_format($item['estimated_unit_cost']);
//                                                $item['total'] = number_format($item['estimated_unit_cost']*$item['quantity']);
//
//                                            }
//                                            return $data;
//                                        }
//                                    })
//                                    ->relationship('items')
//                                    // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
//                                    ->schema([
//                                            Forms\Components\Hidden::make('row_number')->dehydrated(false)
//                                            ->default(fn (Get $get) => count($get->getData()['RequestedItems'] ?? []) )
//                                        ->formatStateUsing(fn ($state, Get $get) => $state ?? count($get->getData()['RequestedItems'] ?? []) ),
//                                        Forms\Components\Select::make('product_id')->columnSpan(3)->label('Product') ->prefix(fn (Get $get) => $get('row_number'))->options(function ($state) {
//                                                if ($state){
//                                                    $products = getCompany()->products->where('id',$state);
//                                                }else{
//                                                    $products= getCompany()->products;
//                                                }
//                                                $data = [];
//                                                foreach ($products as $product) {
//                                                    $data[$product->id] = $product->info;
//                                                }
//                                                return $data;
//                                            })->required()->searchable()->preload(),
//                                        Forms\Components\TextInput::make('description')->label('Description')->columnSpan(10)->required(),
//                                        Forms\Components\Select::make('unit_id')->columnSpan(2)->required()->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id')),
//                                        Forms\Components\TextInput::make('quantity')->numeric()->required(),
//                                        Forms\Components\TextInput::make('unit_price')->numeric()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->label('Unit Price'),
//                                        Forms\Components\TextInput::make('taxes')->default(0)->prefix('%')->numeric()->required()->rules([
//                                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
//                                                    if ($value < 0) {
//                                                        $fail('The :attribute must be greater than 0.');
//                                                    }
//                                                    if ($value > 100) {
//                                                        $fail('The :attribute must be less than 100.');
//                                                    }
//                                                },
//                                            ])->mask(RawJs::make('$money($input)'))->stripCharacters(','),
//                                        Forms\Components\TextInput::make('freights')->default(0)->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
//                                        Forms\Components\Hidden::make('project_id')->label('Project'),
//                                        Forms\Components\Select::make('vendor_id')->live(true)->label('Vendor')->options((getCompany()->parties->where('type', 'vendor')->pluck('name', 'id')))->searchable()->preload()->required()->columnSpan(2)->afterStateUpdated(function (Set $set,$state,Get $get){
//                                            $vendor=Parties::query()->with('currency')->firstWhere('id',$state);
//                                            if ($vendor){
//                                                $set('currency_id',$vendor->currency_id);
//                                                $set('exchange_rate',$vendor->currency?->exchange_rate);
//                                            }
//                                        }),
//                                        Select::make('currency_id')->label('Currency')->afterStateUpdated(function (Set $set, $state,Get $get) {
//                                                $currency = Currency::find($state);
//                                                if ($currency !== null) {
//                                                    $set('exchange_rate', $currency->exchange_rate);
//                                                }
//                                            })->required()->live(true)->options(getCompany()->currencies->pluck('name','id'))->searchable()->preload()->columnSpan(2),
//                                        TextInput::make('exchange_rate')->readOnly()->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
//                                        TextInput::make('total')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->hintAction(Forms\Components\Actions\Action::make('Calculate')->action(function (Set $set,Get $get){
//                                            $freights = $get('taxes') === null ? 0 : (float)$get('taxes');
//                                            $q = $get('quantity');
//                                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
//                                            $price = $get('unit_price') !== null ? str_replace(',', '', $get('unit_price')) : 0;
//                                            $total = (($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)) * (float)$get('exchange_rate');
//                                            $set('total', number_format($total, 2));
//                                        })->icon('heroicon-o-calculator')->color('danger')->iconSize(IconSize::Large))->columnSpan(2)->readOnly(),
//                                    ])->live()
//                                    ->columns(13)->addActionLabel('Add Item')
//                                    ->columnSpanFull(),
                    TableRepeater::make('items')->default(function (Request $request, Set $set) {
                        $record = (PurchaseRequest::query()->with('bid')->firstWhere('id', $request->prno));
                        if ($record?->bid) {
                            $data = [];
                            foreach ($record->bid->quotation?->quotationItems->toArray() as $item) {
                                $prItem = PurchaseRequestItem::query()->firstWhere('id', $item['purchase_request_item_id']);
                                $item['quantity'] = $prItem->quantity;
                                $item['unit_id'] = $prItem->unit_id;
                                $item['description'] = $prItem->description;
                                $item['product_id'] = $prItem->product_id;
                                $item['project_id'] = $prItem->project_id;
                                $item['vendor_id'] = $record->bid->quotation->party_id;
                                $item['currency_id'] = $record->bid->quotation->currecny_id;
                                $item['exchange_rate'] = $record->bid->quotation->currency->exchange_rate;
                                $q = $prItem->quantity;
                                $item['unit_price'] = number_format($item['unit_rate']);
                                $price = $item['unit_rate'];
                                $tax = $item['taxes'];
                                $freights = $item['freights'];

                                $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                $data[] = $item;
                            }

                            return $data;
                        } else {

                            $data = $record?->items->where('status', 'approve')->toArray();
                            if ($data === null) {
                                $data = [];
                            }
                            $i = 1;
                            foreach ($data as &$item) {
                                $item['row_number'] = $i++;
                                $item['unit_price'] = number_format($item['estimated_unit_cost']);
                                $item['total'] = number_format($item['estimated_unit_cost'] * $item['quantity']);

                            }

                            return $data;
                        }
                    })->relationship('items')->addActionLabel('Add')
                        ->headers([
                            Header::make('No')->width('40px'),
                            Header::make('Product/Service')->width('200px')->markAsRequired(),
                            Header::make('Description')->width('190px')->markAsRequired(),
                            Header::make('Unit')->width('130px')->markAsRequired(),
                            Header::make('Quantity')->width('75px')->markAsRequired(),
                            Header::make('Unit Price')->width('100px')->markAsRequired(),
                            Header::make('Taxes')->width('60px')->align(Alignment::Center)->markAsRequired(),
                            Header::make('freights')->width('60px')->align(Alignment::Center)->markAsRequired(),
                            Header::make('vendor')->width('130px')->markAsRequired(),
                            Header::make('currency')->width('100px')->markAsRequired(),
                            Header::make('EX')->label('EX')->width('50px')->align(Alignment::Center)->markAsRequired(),
                            Header::make('total')->width('120px')->align(Alignment::Center)->markAsRequired(),
                        ])->columnSpan('full')->schema([
                            Forms\Components\TextInput::make('row_number')->dehydrated(false)->readOnly()
                                ->default(fn(Get $get) => count($get->getData()['items'] ?? []))
                                ->formatStateUsing(fn($state, Get $get) => $state ?? count($get->getData()['items'] ?? []))->extraAttributes([
                                    'style' => '
                                    border: none !important;
                                    background: transparent !important;
                                    box-shadow: none !important;
                                    pointer-events: none;
                                    padding-left: 0;
                                    font-weight: bold;
                                    color: #555;
                                ',
                                ]),

                            Forms\Components\Select::make('product_id')->columnSpan(3)->label('Product')->options(function ($state) {
                                if ($state) {
                                    $products = getCompany()->products->where('id', $state);
                                } else {
                                    $products = getCompany()->products;
                                }
                                $data = [];
                                foreach ($products as $product) {
                                    $data[$product->id] = $product->info;
                                }
                                return $data;
                            })->required()->searchable()->preload(),
                            Forms\Components\Textarea::make('description')->rows(1)->label('Description')->columnSpan(10)->required(),
                            Forms\Components\Select::make('unit_id')->columnSpan(2)->required()->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id')),
                            Forms\Components\TextInput::make('quantity')->numeric()->required(),
                            Forms\Components\TextInput::make('unit_price')->numeric()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->label('Unit Price'),
                            Forms\Components\TextInput::make('taxes')->default(0)->numeric()->required()->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    if ($value < 0) {
                                        $fail('The :attribute must be greater than 0.');
                                    }
                                    if ($value > 100) {
                                        $fail('The :attribute must be less than 100.');
                                    }
                                },
                            ])->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Forms\Components\TextInput::make('freights')->default(0)->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Forms\Components\Hidden::make('project_id')->label('Project'),
                            Forms\Components\Select::make('vendor_id')->live(true)->label('Vendor')->options((getCompany()->parties->where('type', 'vendor')->pluck('name', 'id')))->searchable()->preload()->required()->columnSpan(2)->afterStateUpdated(function (Set $set, $state, Get $get) {
                                $vendor = Parties::query()->with('currency')->firstWhere('id', $state);
                                if ($vendor) {
                                    $set('currency_id', $vendor->currency_id);
                                    $set('exchange_rate', $vendor->currency?->exchange_rate);
                                }
                            }),
                            Select::make('currency_id')->label('Currency')->placeholder('Select')->afterStateUpdated(function (Set $set, $state, Get $get) {
                                $currency = Currency::find($state);
                                if ($currency !== null) {
                                    $set('exchange_rate', $currency->exchange_rate);
                                }
                            })->required()->live(true)->options(getCompany()->currencies->pluck('name', 'id'))->searchable()->preload()->columnSpan(2),
                            TextInput::make('exchange_rate')->readOnly()->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            TextInput::make('total')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->suffixAction(Forms\Components\Actions\Action::make('Calculate')->action(function (Set $set, Get $get) {
                                $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                $q = $get('quantity');
                                $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                $price = $get('unit_price') !== null ? str_replace(',', '', $get('unit_price')) : 0;
                                $total = (($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)) * (float)$get('exchange_rate');
                                $set('total', number_format($total, 2));
                            })->icon('heroicon-o-calculator')->color('danger')->iconSize(IconSize::Large))->columnSpan(2)->readOnly(),
                        ]) ->extraActions([
                            Forms\Components\Actions\Action::make('Calculate')
                              ->icon('heroicon-o-calculator')->color('danger')->iconSize(IconSize::Large)
                                ->action(function ($state,Set $set): void {
                                    $totals=0;
                                    foreach ($state as &$item){
                                        $freights = $item['freights'] === null ? 0 : (float)$item['freights'];
                                        $q = $item['quantity'];
                                        $tax = $item['taxes'] === null ? 0 : (float)$item['taxes'];
                                        $price = $item['unit_price'] !== null ? str_replace(',', '', $item['unit_price']) : 0;
                                        $total = (($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)) * (float)$item['exchange_rate'];
                                        $item['total']=number_format($total);
                                        $totals+=$total;
                                    }
                                    $set('items',$state);
                                    $set('totals', number_format($totals));
                                    Notification::make('total')
                                        ->success()
                                        ->title('Calculated')
                                        ->send();
                                }),
                        ])
                    ,
                    Section::make()->schema([
                        TextInput::make('totals')->prefix(defaultCurrency()?->name)->inlineLabel()->label('Sub Total')->hintAction(Forms\Components\Actions\Action::make('Calculate')->action(function (Set $set, Get $get) {
                            $total = collect($get('items'))->map(fn($item) => $item['total'] ? str_replace(',', '', $item['total']) : 0)->sum();
                            $set('totals', number_format($total));
                        })->icon('heroicon-o-calculator')->color('danger')->iconSize(IconSize::Large))->dehydrated(false)->readOnly()
                    ])->columns(2)
                ])->columns(3),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_orders_number')->label('PO No')->searchable(query: fn($query ,$search)=> isset($search) ? $query->where('purchase_orders_number',$search) :$query),
                Tables\Columns\TextColumn::make('date_of_po')->label('Date Of PO')->date()->sortable(),
                Tables\Columns\TextColumn::make('items_sum_total')->numeric(2)->sum('items','total')->label('Total'),
                Tables\Columns\TextColumn::make('purchaseRequest.description')->label("PR Description"),
                Tables\Columns\TextColumn::make('purchaseRequest.employee.department.title')->label("PR Department"),
                Tables\Columns\TextColumn::make('purchaseRequest.employee.fullName')->badge()->sortable()->label("PR Requester"),
                Tables\Columns\TextColumn::make('date_of_delivery')->date()->toggleable(isToggledHiddenByDefault: true)->searchable()->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')->toggleable(isToggledHiddenByDefault: true)->searchable(),
                Tables\Columns\ImageColumn::make('approvals')->state(function ($record) {
                    $data = [];
                    foreach ($record->approvals as $approval) {
                        if ($approval->status->value == "Approve") {
                            if ($approval->employee->media->where('collection_name', 'images')->first()?->original_url) {
                                $data[] = $approval->employee->media->where('collection_name', 'images')->first()?->original_url;
                            } else {
                                $data[] = $approval->employee->gender === "male" ? asset('img/user.png') : asset('img/female.png');
                            }
                        }
                    }
                    return $data;
                })->circular()->stacked(),
                Tables\Columns\Textcolumn::make('status')->state(fn($record)=>match ($record->status){
                    'pending'=>"Pending",
                    'Approved'=>"Approved",
                    'rejected'=>"Rending",
                    'GRN'=>'GRN',
                    'Asset'=>'Asset',
                    'Approve Logistic Head'=>'Approve Review',
                    'Asset & Inventory'=>'Asset & Inventory',
                    'Inventory'=>'Inventory',
                    'Approve Verification'=>'Approve Verified',
                })->badge()
                    ->label('Status'),


            ])
            ->filters([
                // Filter::make('created_at')
                //     ->form([DatePicker::make('date')])
                //     // ...
                //     ->indicateUsing(function (array $data): ?string {
                //         if (! $data['date']) {
                //             return null;
                //         }

                //         return 'Created at ' . Carbon::parse($data['date'])->toFormattedDateString();
                //     })
                SelectFilter::make('employee_id')->label('PR Requester')->options(getCompany()->employees->pluck('fullName', 'id'))->searchable()->preload()->query(fn($query,$data)=> isset($data['value'])?  $query->whereHas('purchaseRequest',function ($query)use($data){
                    $query->where('employee_id',$data['value']);
                }):$query),

                SelectFilter::make('id')->searchable()->preload()->options(PurchaseOrder::where('company_id', getCompany()->id)->get()->pluck('purchase_orders_number', 'id'))
                    ->label("Po No"),
                SelectFilter::make('purchase_request_id')->searchable()->preload()->options(PurchaseRequest::where('company_id', getCompany()->id)->where('status','Approval')->get()->pluck('purchase_number', 'id'))
                    ->label("PR No"),
                DateRangeFilter::make('date_of_po')->label('PO Date'),

                // SelectFilter::make('position_id')->searchable()->preload()->options(Position::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                //     ->label('Designation'),


                // SelectFilter::make('duty_id')->searchable()->preload()->options(Duty::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                //     ->label('duty'),

                // TernaryFilter::make('gender')->searchable()->preload()->trueLabel('Man')->falseLabel('Woman'),

                // DateRangeFilter::make('joining_date'),
                // DateRangeFilter::make('leave_date'),
                // Filter::make('base_salary')
                //     ->form([
                //         Forms\Components\Section::make([
                //             TextInput::make('min')->label('Min Base Salary')
                //                 ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                //                 ->numeric(),

                //             TextInput::make('max')->label('Max Base Salary')
                //                 ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                //                 ->numeric(),
                //         ])->columns()
                //     ])->columnSpanFull()
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['min'],
                //                 fn(Builder $query, $date): Builder => $query->where('base_salary', '>=', str_replace(',', '', $date)),
                //             )
                //             ->when(
                //                 $data['max'],
                //                 fn(Builder $query, $date): Builder => $query->where('base_salary', '<=', str_replace(',', '', $date)),
                //             );
                //     }),


            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('Invoice')->visible(fn($record) => $record->status==="GRN" and auth()->user()->can('invoice_purchase::order') and $record->invoice == null)->label('Invoice')->tooltip('Invoice')->icon('heroicon-o-credit-card')->iconSize(IconSize::Medium)->color('warning')->url(fn($record) => PurchaseOrderResource::getUrl('InvoicePurchaseOrder', ['record' => $record->id])),

                Tables\Actions\Action::make('prPDF')->label('')->tooltip('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.po', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->status != 'pending'),
                Tables\Actions\Action::make('Asset')->label('Asset')->url(fn($record) => AssetResource::getUrl('create', ['po' => $record->id]))->visible(fn($record) => $record->items()->whereHas('product', function ($query) {
                        $query->where('product_type', 'unConsumable');
                    })->count() and ($record->status === 'GRN' or $record->status==="Inventory" ))->hidden(fn($record) => $record->status === 'Asset & inventory' or $record->status === 'Asset'),
                Tables\Actions\DeleteAction::make()->visible(fn($record)=>$record->status==="pending" )->action(function ($record){
                    $record->approvals()->delete();
                    $record->purchaseRequest()->update(['status'=>"Approval"]);
                    $record->delete();
                }),
                Tables\Actions\Action::make('Inventory')->label('Inventory')->visible(fn($record) => $record->items()->whereHas('product', function ($query) {
                        $query->where('product_type', 'consumable');
                    })->count() and ($record->status === 'GRN' or $record->status==="Asset" ))->form(function ($record) {
                    $products = Product::query()
                        ->whereIn('id', function ($query) use ($record) {
                            return $query->select('product_id')
                                ->from('purchase_order_items')
                                ->where('purchase_order_id', $record->id);
                        })->where('product_type', 'consumable')
                        ->pluck('title', 'id');

                    return [
                        Placeholder::make('table')->label('')->content(function ($record)use($products) {
                            $data = $record->items->whereIn('product_id',array_keys($products->toArray()))->map(function ($item) {
                                return [
                                    'product' => $item->product->title,
                                    'unit' => $item->unit->title,
                                    'quantity' => $item->quantity,
                                ];
                            })->toArray();

                            $table = '
        <table style="width:100%; border-collapse: collapse; border: 1px solid #ccc;">
            <thead style="background-color: #f0f0f0;">
                <tr>
                    <th style="padding: 8px; border: 1px solid #ccc;color: black !important">Product Title</th>
                    <th style="padding: 8px; border: 1px solid #ccc;color: black !important">Unit</th>
                    <th style="padding: 8px; border: 1px solid #ccc;color: black !important">Quantity</th>
                </tr>
            </thead>
            <tbody>';

                            foreach ($data as $row) {
                                $table .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ccc;text-align: center">' . $row['product'] . '</td>
                <td style="padding: 8px; border: 1px solid #ccc;text-align: center">' . $row['unit'] . '</td>
                <td style="padding: 8px; border: 1px solid #ccc;text-align: center">' . number_format($row['quantity']) . '</td>
            </tr>';
                            }

                            $table .= '
            </tbody>
        </table>';

                            return new HtmlString($table);
                        }),


                        Repeater::make('inventories')->schema([
                            Select::make('product_id')->label('Product')->options($products)->searchable()->preload()->required(),
                            Select::make('warehouse_id')->label('Warehouse Location')->required()->options(getCompany()->warehouses()->where('type', 1)->pluck('title', 'id'))->searchable()->preload(),
                            TextInput::make('quantity')->numeric()->required(),
                            Forms\Components\Textarea::make('description')->columnSpanFull()->required(),
                        ])->columns(3)->formatStateUsing(function ($record) use ($products) {
                            $data = [];
                            foreach ($record->items->whereIn('product_id', array_keys($products->toArray())) as $item) {
                                $data[] = ['product_id' => $item->product_id, 'description' => $item->description, 'warehouse_id' => null, 'quantity' => null];
                            }
                            return $data;
                        })->reorderable(false)
                    ];
                })->action(function ($data, $record) {
                    $productsAll= Product::query()->with('unit')
                        ->whereIn('id', function ($query) use ($record) {
                            return $query->select('product_id')
                                ->from('purchase_order_items')
                                ->where('purchase_order_id', $record->id);
                        })->where('product_type', 'consumable')->get();

                    $products =$productsAll->pluck('title', 'id');
//                    dd($productsAll,$products,$data['inventories']);
                    $validateData = [];
                    foreach ($data['inventories'] as $inventory) {
                        $quantity=(int)$inventory['quantity'];
                        if (key_exists($inventory['product_id'], $validateData)) {
                            $lastQuantity = $validateData[$inventory['product_id']];
                            $validateData[$inventory['product_id']] = $lastQuantity + $quantity;
                        } else {
                            $validateData[$inventory['product_id']] = $quantity;
                        };
                    }

                    foreach ($record->items->whereIn('product_id',array_keys($products->toArray())) as $item) {
                        if (key_exists($item->product_id, $validateData)) {
                            $quantity = $validateData[$item->product_id];
                            if ($quantity != $item->quantity) {
                                Notification::make('danger')->danger()->title('Quantity Not Valid')->send();
                                return;
                            }
                        }
                    }

                    if (count(array_keys($validateData))!= $record->items->whereIn('product_id',array_keys($products->toArray()))->count()){
                        Notification::make('danger')->danger()->title('Product Not Valid')->send();
                    }

                    foreach ($data['inventories'] as $inventory) {
                        $inv = Inventory::query()->where('warehouse_id', $inventory['warehouse_id'])->where('product_id', $inventory['product_id'])->first();
                        if (!$inv) {
                            $inv = Inventory::query()->create([
                                'warehouse_id' => $inventory['warehouse_id'],
                                'product_id' => $inventory['product_id'],
                                'quantity' => 0,
                                'company_id' => $record->company_id
                            ]);
                        }

                        $item= $record->items->firstWhere('product_id',$inventory['product_id']);
                        $quantity=(int)$inventory['quantity'];

                        if (isset($item->unit) and $item->unit->is_package){

                            $package=$item->unit;
                            if ($package){
                                $quantity=$quantity*$package->items_per_package;
                            }

                        }

                        Stock::query()->create([
                            'inventory_id' => $inv->id,
                            'employee_id' => getEmployee()->id,
                            'quantity' => $quantity,
                            'description' => $inventory['description'],
                            'type' => 1,
                            'purchase_order_id' => $record->id
                        ]);

                        $inv->update(['quantity' => $inv->quantity + $quantity]);
                    }
                    if ($record->status === "GRN") {
                        $record->update(['status' => 'inventory']);
                    } else {
                        $record->update(['status' => 'Asset & Inventory']);
                    }

                    Notification::make('success')->success()->title('Successfully')->send();
                })->modalWidth(MaxWidth::SixExtraLarge)->hidden(fn($record) => $record->status === 'Asset & inventory' or $record->status === 'Inventory' or  $record->status === 'pending' or $record->status === 'rejected')

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
            RelationManagers\PurchaseOrderItemRelationManager::class
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return PurchaseRequest::query()->where('company_id',getCompany()->id)->where('status','Approval')->whereHas('purchaseOrder',function (){},'!=')->count();
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger'; // TODO: Change the autogenerated stub
    }

    /**
     * @return string|null
     */
    public static function getNavigationBadgeTooltip(): ?string
    {
        return "Count of PR ready for PO";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}/view'),
            'InvoicePurchaseOrder' => Pages\InvoicePurchaseOrder ::route('/InvoicePurchaseOrder/{record}'),
        ];
    }
}
