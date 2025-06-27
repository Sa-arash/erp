<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\Account;
use App\Models\Bid;
use App\Models\Employee;
use App\Models\Parties;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\Transaction;
use App\Models\Unit;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class PurchaseRequestResource extends Resource
    implements HasShieldPermissions
{
    protected static ?string $model = PurchaseRequest::class;

    protected static ?int $navigationSort = 0;
    protected static ?string $pluralLabel = 'Purchase Request';
    protected static ?string $modelLabel = 'Purchase Request';
    protected static ?string $Label = 'Purchase Request';
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $navigationIcon = 'heroicon-c-document-arrow-down';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'duplicate'
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'Requested')->where('company_id',getCompany()->id)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')->schema([
                    Forms\Components\Select::make('employee_id')->live()
                        ->searchable()
                        ->preload()
                        ->label('Requested By')
                        ->required()
                        ->options(getCompany()->employees->pluck('fullName', 'id'))
                        ->default(fn() => auth()->user()->employee->id),

                    Forms\Components\TextInput::make('purchase_number')->readOnly()
                        ->label('PR Number')->prefix('ATGT/UNC/')->default(function () {
                            $puncher = PurchaseRequest::query()->where('company_id', getCompany()->id)->latest()->first();
                            if ($puncher) {
                                return  generateNextCodePO($puncher->purchase_number);
                            } else {
                                return "00001";
                            }
                        })
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })->hintAction(\Filament\Forms\Components\Actions\Action::make('update')->label('Update NO')->action(function (Set $set){
                            $puncher= PurchaseRequest::query()->where('company_id',getCompany()->id)->latest()->first();
                            if ($puncher){
                                $set('purchase_number',generateNextCodePO($puncher->purchase_number));
                            }else{
                                $set('purchase_number','00001');
                            }
                        }))
                        ->required()
                        ->numeric(),
                    Forms\Components\DateTimePicker::make('request_date')->readOnly()->default(now())->label('Request Date')->required(),
                    Forms\Components\Hidden::make('status')->label('Status')->default('Requested')->required(),
                    Select::make('currency_id')->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->live(true),
                    Forms\Components\TextInput::make('description')->label('Description')->columnSpanFull(),
                    Repeater::make('Requested Items')
                        ->addActionLabel('Add')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('type')->required()->options(['Service','Product'])->default(1)->searchable(),
                            Select::make('department_id')->label('Section')->columnSpan(['default'=>8,'md'=>2,'xl'=>2,'2xl'=>1])->live()->options(getCompany()->departments->pluck('title','id'))->searchable()->preload(),
                            Forms\Components\Select::make('product_id')->columnSpan(['default'=>8,'md'=>2])->label('Product/Service')->options(function (Get $get) {
                                    if ($get('department_id')){
                                        $data=[];
                                        $products=getCompany()->products()->where('product_type',$get('type')==="0"?'=':'!=' ,'service')->where('department_id',$get('department_id'))->pluck('title', 'id');
                                        $i=1;
                                        foreach ($products as $key=> $product){

                                            $data[$key]=$i.". ". $product;
                                            $i++;
                                        }
                                        return $data ;
                                    }
                                })->required()->searchable()->preload()->afterStateUpdated(function (Forms\Set $set,$state){
                                    $product=Product::query()->firstWhere('id',$state);
                                    if ($product){
                                        $set('unit_id',$product->unit_id);
                                    }
                                })->live(true)->getSearchResultsUsing(fn (string $search,Get $get): array => Product::query()->where('department_id',$get('department_id'))->where('company_id',getCompany()->id)->where('title','like',"%{$search}%")->orWhere('second_title','like',"%{$search}%")->pluck('title', 'id')->toArray())->getOptionLabelsUsing(function(array $values){
                                    $data=[];
                                    $products=getCompany()->products->whereIn('id', $values)->pluck('title', 'id');
                                    $i=1;
                                    foreach ($products as $key=> $product){
                                        $data[$key]=$i.". ". $product;
                                        $i++;
                                    }
                                    return $data ;
                                }),
                            Forms\Components\Select::make('unit_id')->columnSpan(['default'=>8,'md'=>2,'xl'=>2])->createOptionForm([
                                Forms\Components\TextInput::make('title')->label('Unit Name')->unique('units', 'title')->required()->maxLength(255),
                                Forms\Components\Toggle::make('is_package')->live()->required(),
                                Forms\Components\TextInput::make('items_per_package')->numeric()->visible(fn(Get $get) => $get('is_package'))->default(null),
                            ])->createOptionUsing(function ($data) {
                                $data['company_id'] = getCompany()->id;
                                Notification::make('success')->success()->title('Create Unit')->send();
                                return  Unit::query()->create($data)->getKey();
                            })->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                            Forms\Components\TextInput::make('quantity')->columnSpan(['default'=>8,'md'=>2,'2xl'=>1])->required()->live()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Forms\Components\TextInput::make('estimated_unit_cost')->columnSpan(['default'=>8,'md'=>2,'2xl'=>1])->label('EST Unit Cost')->live(true)->numeric()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Forms\Components\Select::make('project_id')->columnSpan(['default'=>8,'md'=>2,'2xl'=>1])->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                            Placeholder::make('total')->columnSpan(['default'=>8,'md'=>1,'xl'=>1])
                                ->content(fn($state, Get $get) => number_format(((int)str_replace(',', '', $get('quantity'))) * ((float)str_replace(',', '', $get('estimated_unit_cost'))),2)
//                                    .Currency::query()->firstWhere('id',dd($get('currency_id'))
                                    ),
                            Forms\Components\Hidden::make('company_id')->default(Filament::getTenant()->id)->required(),
                            Forms\Components\Textarea::make('description')->label(' Product Name and Description')->columnSpan(['default'=>4,'sm'=>3,'md'=>3,'xl'=>4])->required(),
                            MediaManagerInput::make('document')->orderable(false)->folderTitleFieldName("purchase_request_id")->disk('public')->schema([])->defaultItems(0)->maxItems(1) ->columnSpan(['default'=>4,'sm'=>2,'md'=>2,'xl'=>3]),
                        ])
                        ->columns(['default'=>4,'sm'=>6,'md'=>6,'xl'=>8])
                        ->columnSpanFull(),
                    // Section::make('estimated_unit_cost')->schema([
                    //     Placeholder::make('Total')->live()
                    //     ->content(function (Get $get) {
                    //         $sum = 0;
                    //         foreach($get('Requested Items') as $item)
                    //         {
                    //             $sum += (int)$item['quantity']*(int)$item['estimated_unit_cost'];
                    //         }
                    //         return $sum;
                    //     } )
                    // ])
                ])->columns(4)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex()->label('No'),
                Tables\Columns\TextColumn::make('employee.fullName')->sortable()->tooltip(fn($record)=>$record->employee->position->title)
                    ->label('Requested By')->searchable(),
                Tables\Columns\TextColumn::make('purchase_number')->sortable()->prefix('ATGT/UNC/')->label('PR No')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('PR Description')->tooltip(fn($record) => $record->description)->limit(30),
                Tables\Columns\TextColumn::make('department')->state(fn($record) => $record->employee->department->title),
                Tables\Columns\TextColumn::make('request_date')->label('Request Date')->dateTime()->sortable(),
                // Tables\Columns\TextColumn::make('location')->state(fn($record) => $record->employee?->structure?->title)->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge(),
                Tables\Columns\TextColumn::make('bid.quotation.party.name')->sortable()->label('Vendor'),
                Tables\Columns\TextColumn::make('total')->alignCenter()->label('Total EST Price ' )
                    ->state(function ($record) {
                        $total = 0;
                        foreach ($record->items as $item) {
                            $total += $item->quantity * $item->estimated_unit_cost;
                        }
                        return number_format($total, 2) . " " . $record->currency?->symbol;
                    })->numeric(),

                Tables\Columns\TextColumn::make('is_quotation')->sortable()->alignCenter()->label(' Quotation Status')->badge()->state(fn($record) => $record->is_quotation ? "Yes" : "No")->color(fn($record) => $record->is_quotation ? "danger" : "secondary"),
                Tables\Columns\TextColumn::make('bid.total_cost')->alignCenter()->label('Total Final Price')->numeric(),
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

            ])

            ->filters([
                Tables\Filters\SelectFilter::make('department')->searchable()->preload()->label('Department')->options(getCompany()->departments()->pluck('title','id'))->query(fn($query,$data)=>isset($data['value'])? $query->whereHas('employee',function ($query)use($data){
                    return $query->where('department_id',$data['value']);
                }):$query),
                SelectFilter::make('employee_id')->label('Requestor')->options(getCompany()->employees->pluck('fullName', 'id'))->searchable()->preload(),
                SelectFilter::make('id')->searchable()->preload()->options(PurchaseRequest::where('company_id', getCompany()->id)->get()->pluck('purchase_number', 'id'))
                    ->label("PR NO"),

                    Filter::make('vendor')
                    ->form([
                        Select::make('vendor_id')
                            ->options(Parties::where('company_id', getCompany()->id)
                                ->whereNotNull('account_code_vendor')
                                ->pluck('name', 'id'))
                            ->label("Vendor")->searchable()->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['vendor_id'],
                            fn(Builder $query, $vendorId): Builder => $query->whereHas('bid.quotation.party', function (Builder $query) use ($vendorId) {
                                $query->where('id', $vendorId);
                            })
                        );
                    }),

                DateRangeFilter::make('request_date'),

            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('prPDF')->label('Print  Preview')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.purchase', ['id' => $record->id]))->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Order')
                    ->disabled(fn() => getPeriod() === null)->tooltip(fn() => getPeriod() !== null ?'': 'Financial Period Required')
                    ->visible(fn($record) => $record->status->value == 'Approval')
                    ->icon('heroicon-s-shopping-cart')
                    ->url(fn($record) => PurchaseOrderResource::getUrl('create') . "?prno=" . $record->id),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('prQuotation')->visible(fn($record) => $record->is_quotation)->color('warning')->label('RFQ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.quotation', ['id' => $record->id]))->openUrlInNewTab(),
                    Tables\Actions\Action::make('insertQuotation')->modalWidth(MaxWidth::Full)->icon('heroicon-s-newspaper')->color('info')->label('Quotation')->visible(fn($record) => $record->is_quotation)->form(function ($record) {
                        return [
                            Section::make([
                                Forms\Components\TextInput::make('purchase_number')->prefix('ATGT/UNC')->readOnly()->label('PR NO')->default($record->purchase_number),
                                Forms\Components\Select::make('party_id')->createOptionUsing(function ($data) {
                                    $parentAccount = Account::query()->where('id', $data['parent_vendor'])->where('company_id', getCompany()->id)->first();
                                    $account = Account::query()->create([
                                        'name' =>  $data['name'],
                                        'type' => 'creditor',
                                        'code' => $parentAccount->code . $data['account_code_vendor'],
                                        'level' => 'detail',
                                        'group' => 'Liabilitie',
                                        'parent_id' => $parentAccount->id,
                                        'built_in' => false,
                                        'company_id' => getCompany()->id,
                                        'currency_id' => $data['currency_id']
                                    ]);
                                    $data['account_vendor'] = $account->id;
                                    $data['company_id'] = getCompany()->id;
                                    $data['type'] = 'vendor';
                                    return Parties::query()->create($data)->getKey();
                                })->createOptionForm([
                                    Forms\Components\Section::make([
                                        Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                                        Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                                        Forms\Components\TextInput::make('email')->email()->maxLength(255),
                                        Forms\Components\Textarea::make('address')->columnSpanFull(),
                                        SelectTree::make('parent_vendor')->disabledOptions(function () {
                                            return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                                        })->default(getCompany()?->vendor_account)->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Vendor Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id)),
                                        Forms\Components\TextInput::make('account_code_vendor')->prefix(fn(Get $get) => Account::find($get('parent_vendor'))?->code)->default(function () {
                                            if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()) {
                                                return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()->account_code_vendor);
                                            } else {
                                                return "001";
                                            }
                                        })->required()->maxLength(255),
                                        getSelectCurrency(),
                                    ])->columns(3)->model(Parties::class),
                                ])->label('Vendor')->options(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->get()->pluck('info', 'id'))->searchable()->preload()->required(),
                                Forms\Components\DatePicker::make('date')->default(now())->required(),
                                Forms\Components\Select::make('employee_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Logistic By'),
                                Forms\Components\Select::make('employee_operation_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Quality Control'),
                                getSelectCurrency(),
                                Forms\Components\FileUpload::make('file')->downloadable()->columnSpanFull(),
                                Forms\Components\Textarea::make('description')->columnSpanFull()->nullable()
                            ])->columns(6),
                            Repeater::make('Requested Items')->required()
                                ->addActionLabel('Add')
                                ->schema([
                                    Forms\Components\Select::make('purchase_request_item_id')->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->label('Product')->options(function () use ($record) {
                                            $products = $record->items->where('status', 'approve');
                                            $data = [];
                                            foreach ($products as $product) {
                                                $data[$product->id] = $product->product->title . " (" . $product->product->sku . ")";
                                            }
                                            return $data;
                                        })->required()->searchable()->preload(),
                                    Forms\Components\TextInput::make('quantity')->readOnly()->live()->required()->numeric(),
                                    Forms\Components\TextInput::make('unit_rate')->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        if ($get('quantity') and $get('unit_rate')) {
                                            $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                            $q = $get('quantity');
                                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                            $price = $state !== null ? str_replace(',', '', $state) : 0;
                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        }
                                    })->live(true)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    Forms\Components\TextInput::make('taxes')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                        $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                        $q = $get('quantity');
                                        $tax = $state === null ? 0 : (float)$state;
                                        $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                        $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                    })->live(true)->prefix('%')->numeric()->maxValue(100)->required()->rules([
                                        fn(): \Closure => function (string $attribute, $value, \Closure $fail) {
                                            if ($value < 0) {
                                                $fail('The :attribute must be greater than 0.');
                                            }
                                            if ($value > 100) {
                                                $fail('The :attribute must be less than 100.');
                                            }
                                        },
                                    ])->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    Forms\Components\TextInput::make('freights')->prefix('%')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                        $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                        $q = $get('quantity');
                                        $freights = $state === null ? 0 : (float)$state;
                                        $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                        $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                    })->live(true)->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    Forms\Components\TextInput::make('total')->readOnly()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    Forms\Components\Textarea::make('description')->columnSpanFull()->readOnly()->label('Product Name and Description'),
                                ])->formatStateUsing(function () use ($record) {
                                    $data = [];
                                    foreach ($record->items->where('status', 'approve') as $item) {
                                        $data[] = ['purchase_request_item_id' => $item->id, 'description' => $item->description, 'quantity' => $item->quantity, 'unit_rate' => 0, 'taxes' => 0, 'freights' => 0];
                                    }
                                    return $data;
                                })
                                ->columns(6)->addable(false)->columnSpanFull()

                        ];
                    })->action(function ($data, $record) {

                        $id = getCompany()->id;
                        $quotation = Quotation::query()->create([
                            'purchase_request_id' => $record->id,
                            'party_id' => $data['party_id'],
                            'date' => $data['date'],
                            'employee_id' => $data['employee_id'],
                            'employee_operation_id' => $data['employee_operation_id'],
                            'company_id' => $id,
                            'currency_id' => $data['currency_id']
                        ]);

                        foreach ($data['Requested Items'] as $item) {
                            $quotation->quotationItems()->create([
                                'purchase_request_item_id' => $item['purchase_request_item_id'],
                                'unit_rate' => $item['unit_rate'],
                                'date' => $data['date'],
                                'freights' => $item['freights'],
                                'taxes' => $item['taxes'],
                                'company_id' => $id,
                                'total' => $item['total']

                            ]);
                        }
                        Notification::make('add quotation')->success()->title('Quotation Added')->send()->sendToDatabase(auth()->user());
                    }),
                    Tables\Actions\Action::make('bid')->label('Bid Summery')->color('success')->icon('heroicon-c-check-badge')->form(function ($record) {
                        return [
                            Section::make([
                                Forms\Components\DatePicker::make('opening_date')->default(now())->required(),
                                Select::make('quotation_id')->options(function () use ($record) {
                                    $data = [];
                                    $quotations = Quotation::query()->where('purchase_request_id', $record->id)->get();
                                    foreach ($quotations as $quotation) {
                                        $data[$quotation->id] = $quotation->party?->name;
                                    }
                                    return $data;
                                })->required()->label('Quotation Selected')->preload()->searchable()->live(),
                                Select::make('position_procurement_controller')->label(' Procurement and Logistics ')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),
                                Select::make('procurement_committee_members')->label(' Committee Members')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),

                            ])->columns(4),
                            Placeholder::make('content')->content(function (Get $get) use ($record) {
                                $trs = "";
                                $totalTrs = "
                                <tr>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                ";
                                $vendors = '';
                                $ths = '';
                                foreach ($record->quotations as $quotation) {
                                    $vendor = $quotation->party->name . "'s Quotation" . " " . $quotation->currency->symbol;
                                    if ($get('quotation_id')==$quotation->id){
                                        $vendors .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(82,178,49)'>{$vendor}</th>";
                                    }else{
                                        $vendors .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>{$vendor}</th>";
                                    }
                                    $totalSum = 0;

                                    foreach ($quotation->quotationItems as $quotationItem) {
                                        $totalSum += $quotationItem->total;
                                    }
                                    $totalSum = number_format($totalSum);
                                    $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> Total {$totalSum}</td>";
                                }
                                $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> </td></tr>";
                                foreach ($record->items->where('status', 'approve') as $item) {
                                    $product = $item->product->title . " (" . $item->product->sku . ")";
                                    $description = $item->description;
                                    $quantity = $item->quantity;
                                    $tr = "<tr>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>$product</td>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>$description</td>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>{$item->unit->title}</td>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>$quantity</td>

                                             ";
                                    foreach ($item->quotationItems as $quotationItem) {
                                        $total = number_format($quotationItem->total);
                                        $rate = number_format($quotationItem->unit_rate);
                                        $tax = number_format($quotationItem->taxes);

                                        $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>  {$rate} Per Unit | {$tax}%  Tax |  {$total} Total</td>";
                                    }
                                    $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'></td>";
                                    $tr .= "</tr>";
                                    $trs .= $tr;
                                }

                                $table = "
<table style='border-collapse: collapse;width: 100%'>
    <thead>
        <tr>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Item</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Item Description</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Unit</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Qty</th>
            $vendors
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Remarks</th>
        </tr>

    </thead>
    <tbody>
        {$trs}
        $totalTrs
    </tbody>
</table>";
                                return new HtmlString($table);
                            })->columnSpanFull(),
                        ];
                    })->action(function ($data, $record) {
                        $data['company_id'] = getCompany()->id;
                        $data['purchase_request_id'] = $record->id;
                        $quotation = Quotation::query()->firstWhere('id', $data['quotation_id']);
                        $totalSum = 0;
                        foreach ($quotation->quotationItems as $quotationItem) {
                            $totalSum += $quotationItem->item->quantity * $quotationItem->unit_rate;
                        }
                        $data['total_cost'] = $totalSum;
                        Bid::query()->create($data);
                        Notification::make('make bid')->success()->title('Submitted Successfully')->send()->sendToDatabase(auth()->user());
                    })->modalWidth(MaxWidth::Full)->visible(fn($record) => $record->quotations->count() > 0 and empty($record->bid)),
                ]),

                Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->status->name === "Requested")->action(function ($record){
                    $record->approvals()->delete();
                    $record->delete();
                }),
                Tables\Actions\Action::make('Duplicate')->visible(fn()=>auth()->user()->can('duplicate_purchase::request'))->iconSize(IconSize::Large)->icon('heroicon-o-clipboard-document-check')->label('Duplicate')->url(fn($record)=>PurchaseRequestResource::getUrl('replicate',['tk'=>'resource','id'=>$record->id]))

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\QuotationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseRequests::route('/'),
            'create' => Pages\CreatePurchaseRequest::route('/create'),
//            'edit' => Pages\EditPurchaseRequest::route('/{record}/edit'),
            'view' => Pages\ViewPurcheseRequest::route('/{record}/view'),
            'replicate' => Pages\Replicate::route('/{id}/replicate/{tk}'),
        ];
    }
}
