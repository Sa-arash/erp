<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Currency;
use App\Models\Inventory;
use App\Models\Package;
use App\Models\Parties;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Stock;
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
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
            'invoice',
        ];
    }



    public static function canCreate(): bool
    {
        return getPeriod() != null;
    }
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationGroup = 'Logistic Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

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

                                                    $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                                    $data[] = $item;
                                                }
                                                $set('RequestedItems', $data);
                                                $set('vendor_id', $record->bid->quotation->party_id);
                                                $set('currency_id', $record->bid->quotation->currency_id);
                                            } else {
                                                $data = [];
                                                foreach ($record->items->where('status', 'approve')->toArray() as $item) {
                                                    $item['taxes'] = 0;
                                                    $item['freights'] = 0;
                                                    $data[] = $item;
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

                                                    $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                                    $data[] = $item;
                                                }
                                                $set('RequestedItems', $data);
                                                $set('vendor_id', $record->bid->quotation->party_id);
                                                $set('currency_id', $record->bid->quotation->currency_id);
                                                $set('exchange_rate', $record->bid->quotation->currency->exchange_rate);
                                            } else {
                                                $data = [];
                                                foreach ($record->items->where('status', 'approve')->toArray() as $item) {
                                                    $item['taxes'] = 0;
                                                    $item['freights'] = 0;
                                                    $data[] = $item;
                                                }
                                                $set('RequestedItems', $data);
                                            }
                                        }
                                    })
                                    ->options(function (){
                                        $data=[];
                                        foreach (getCompany()->purchaseRequests()->where('status','Approval')->whereHas('purchaseOrder',function (){},'!=')->orderBy('id', 'desc')->get() as $item){
                                            $data[$item->id]=  $item->purchase_number.'('.$item->employee?->fullName.')';
                                        }
                                        return $data;
                                    }),

                                Forms\Components\DateTimePicker::make('date_of_po')->default(now())
                                    ->label('Date of PO')->afterOrEqual(now()->startOfHour())
                                    ->required(),

                                Forms\Components\Select::make('vendor_id')->label('Vendor')
                                    ->options((getCompany()->parties->where('type', 'vendor')->pluck('info', 'id')))
                                    ->searchable()->preload()->required(),
                                Select::make('currency_id')->live()->label('Currency')

                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $currency = Currency::find($state);
                                        if ($currency !== null) {
                                            // dd($currency,$currency->exchange_rate);
                                            $set('exchange_rate', $currency->exchange_rate);
                                            $set('currency', $currency->symbol);
                                        }
                                    })

                                    ->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
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
                                        Section::make([
                                            TextInput::make('name')->required()->maxLength(255),
                                            TextInput::make('symbol')->required()->maxLength(255),
                                            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                        ])->columns(3)
                                    ]),
                                Forms\Components\TextInput::make('exchange_rate')->readOnly()
                                    ->required()->default(1)
                                    ->numeric(),
                                Forms\Components\Select::make('prepared_by')->live()
                                    ->searchable()
                                    ->preload()
                                    ->label('Processed By')
                                    ->options(getCompany()->employees->where('id',getEmployee()->id)->pluck('fullName', 'id'))
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
                                Repeater::make('RequestedItems')->defaultItems(1)->required()
                                    ->default(function (Request $request, Set $set) {
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
                                                $q = $prItem->quantity;
                                                $item['unit_price'] = number_format($item['unit_rate']);
                                                $price = $item['unit_rate'];
                                                $tax = $item['taxes'];
                                                $freights = $item['freights'];

                                                $item['total'] = number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100));
                                                $data[] = $item;
                                            }
                                            $set('vendor_id', $record->bid->quotation->party_id);
                                            $set('currency_id', $record->bid->quotation->currency_id);
                                            $set('exchange_rate', $record->bid->quotation->currency->exchange_rate);

                                            return  $data;
                                        } else {
                                            return $record?->items->where('status', 'approve')->toArray();
                                        }
                                    })
                                    ->relationship('items')
                                    // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
                                    ->schema([
                                        Forms\Components\Select::make('product_id')->columnSpan(3)
                                            ->label('Product')->options(function ($state) {
                                                $products = getCompany()->products->where('id',$state);
                                                $data = [];
                                                foreach ($products as $product) {
                                                    $data[$product->id] = $product->info;
                                                }
                                                return $data;
                                            })->required()->searchable()->preload(),
                                        Forms\Components\TextInput::make('description')->label('Description')->columnSpan(3)->required(),
                                        Forms\Components\Select::make('unit_id')->columnSpan(2)->required()->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id')),
                                        Forms\Components\TextInput::make('quantity')->numeric()->required()->live(true),
                                        Forms\Components\TextInput::make('unit_price')->prefix(fn(Get $get)=>$get->getData()['currency'])->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $freights = $get('taxes') === null ? 0 : (float) $get('taxes');
                                            $q = $get('quantity');
                                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                            $price = $state !== null ? str_replace(',', '', $state) : 0;
                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        })->live(true)
                                            // ->readOnly(fn(Get $get)=>$record=PurchaseRequest::query()->with('bid')->firstWhere('id',$state);)
                                            ->numeric()
                                            ->required()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')->label('Final Price'),
                                        Forms\Components\TextInput::make('taxes')->default(0)->afterStateUpdated(function ($state, Set $set, Get $get) {

                                            $freights = intval($get('freights') == null ? 0 : (float)$get('freights'));

                                            $q = intval($get('quantity'));

                                            $tax = intval($state === null ? 0 : (float)$state);
                                            $price = intval($get('unit_price') != null ? str_replace(',', '', $get('unit_price')) : 0);



                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        })->live(true)
                                            ->prefix('%')
                                            ->numeric()
                                            ->required()
                                            ->rules([
                                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                                    if ($value < 0) {
                                                        $fail('The :attribute must be greater than 0.');
                                                    }
                                                    if ($value > 100) {
                                                        $fail('The :attribute must be less than 100.');
                                                    }
                                                },
                                            ])
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(','),
                                        Forms\Components\TextInput::make('freights')->default(0)->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $freights = $state === null ? 0 : (float) $state;
                                            $q = intval($get('quantity'));
                                            $tax = intval($get('taxes') === null ? 0 : (float)$get('taxes'));
                                            $price = intval($get('unit_price') !== null ? str_replace(',', '', $get('unit_price')) : 0);

                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        })->live(true)
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(','),

                                        Forms\Components\Hidden::make('project_id')

                                            ->label('Project'),

                                        TextInput::make('total')->readOnly(),

                                    ])
                                    ->columns(13)
                                    ->columnSpanFull(),
                            ])->columns(3),
                        // ]),

                    // Wizard\Step::make('Invoice')
                    //     ->schema([

                    //         Group::make()->relationship('invoice')->schema([

                    //             Forms\Components\Hidden::make('company_id')->default(getCompany()->id)->required(),
                    //             Forms\Components\Section::make([
                    //                 Forms\Components\TextInput::make('number')
                    //                     ->columnSpan(1)
                    //                     ->default(getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1)->label('Voucher Number')->required()->maxLength(255)->readOnly(),
                    //                 Forms\Components\TextInput::make('name')
                    //                     ->columnSpan(3)
                    //                     ->label('Voucher Title')->required()->maxLength(255),
                    //                 Forms\Components\TextInput::make('reference')
                    //                     ->columnSpan(1)
                    //                     ->maxLength(255),
                    //                 Forms\Components\DatePicker::make('date')
                    //                     ->columnSpan(2)
                    //                     ->required()->default(now()),
                    //                 Forms\Components\FileUpload::make('document')->placeholder('Browse')->extraInputAttributes(['style' => 'height:30px!important;'])
                    //                     ->nullable(),
                    //                 Placeholder::make('total :')->live()->content(function (Get $get) {
                    //                     if ($get->getData()['RequestedItems']) {
                    //                         $produtTotal = array_map(function ($item) {
                    //                             // dd($item);
                    //                             try {
                    //                                 //code...
                    //                                 return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100));
                    //                             } catch (\Throwable $th) {
                    //                                 //throw $th;
                    //                                 return null;
                    //                             }
                    //                         }, $get->getData()['RequestedItems']);

                    //                         return  collect($produtTotal)->sum() ? number_format(collect($produtTotal)->sum()) : '?';
                    //                     }
                    //                 })->inlineLabel()
                    //             ])->columns(8),

                    //             Forms\Components\Section::make([
                    //                 Forms\Components\Repeater::make('transactions')->label('')->relationship('transactions')->schema([
                    //                     Forms\Components\Hidden::make('company_id')->default(getCompany()->id)->required(),
                    //                     SelectTree::make('account_id')->formatStateUsing(function ($state, Forms\Set $set) {
                    //                         $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                    //                         if ($account) {
                    //                             $set('currency_id', $account->currency_id);
                    //                             $set('exchange_rate', number_format($account->currency->exchange_rate));
                    //                             $set('isCurrency', 1);
                    //                             return $state;
                    //                         }
                    //                         $set('isCurrency', 0);
                    //                         return $state;
                    //                     })->afterStateUpdated(function ($state, Forms\Set $set) {
                    //                         $query = Account::query()->find($state);
                    //                         // dd($query);
                    //                         if ($query) {

                    //                             if ($query->type == 'debtor') {
                    //                                 $set('cheque.type', 0);
                    //                             } else {

                    //                                 $set('cheque.type', 1);
                    //                             }

                    //                             if ($query->has_cheque == 1) {
                    //                                 $set('Cheque', true);
                    //                             } else {
                    //                                 $set('Cheque', false);
                    //                             }
                    //                         } else {
                    //                             $set('Cheque', false);
                    //                         }

                    //                         $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                    //                         if ($account) {
                    //                             $set('currency_id', $account->currency_id);
                    //                             $set('exchange_rate', number_format($account->currency->exchange_rate));
                    //                             return $set('isCurrency', 1);
                    //                         }
                    //                         return $set('isCurrency', 0);
                    //                     })->live()->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('group', 'asset')->where('company_id', getCompany()->id))->searchable(),
                    //                     Forms\Components\TextInput::make('description')->required(),

                    //                     Forms\Components\TextInput::make('debtor')->prefix(defaultCurrency()->symbol)->mask(RawJs::make('$money($input)'))->readOnly()->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                    //                         ->rules([
                    //                             fn(Get $get): Closure => function (string $attribute, $value, Closure $fail, $operation) use ($get) {
                    //                                 if ($operation == "create") {



                    //                                     if ($get('debtor') != 0) {
                    //                                         $fail('The debtor field must be zero.');
                    //                                     }
                    //                                 } else {
                    //                                     if ($get('debtor') == 0 && $get('creditor') == 0) {
                    //                                         $fail('Only one of these values can be zero.');
                    //                                     } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                    //                                         $fail('At least one of the values must be zero.');
                    //                                     }
                    //                                 }
                    //                             },
                    //                         ]),
                    //                     Forms\Components\TextInput::make('creditor')->prefix(defaultCurrency()->symbol)
                    //                         ->readOnly(function (Get $get) {
                    //                             return $get('isCurrency');
                    //                         })
                    //                         ->live(true)->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {

                    //                             $set('cheque.amount', $state);
                    //                         })
                    //                         ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                    //                         ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                    //                         ->rules([
                    //                             fn(Get $get): Closure => function (string $attribute, $value, Closure $fail, $operation) use ($get) {

                    //                                 if ($operation == "create") {
                    //                                     if ($get('creditor') == 0) {
                    //                                         $fail('The creditor field must be not zero.');
                    //                                     } else {

                    //                                         // dd(()));
                    //                                         $produtTotal = array_map(function ($item) {
                    //                                             // dd($item);
                    //                                             return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100));
                    //                                         }, $get->getData()['RequestedItems']);

                    //                                         $invoiceTotal = array_map(function ($item) {
                    //                                             // dd($item);
                    //                                             return (str_replace(',', '', $item['creditor']));
                    //                                         }, $get->getData()['invoice']['transactions']);

                    //                                         $productSum = collect($produtTotal)->sum();
                    //                                         $invoiceSum = collect($invoiceTotal)->sum();

                    //                                         if ($invoiceSum != $productSum) {
                    //                                             $remainingAmount = $productSum - $invoiceSum;
                    //                                             $fail("The paid amount does not match the total price. Total amount:" . number_format($productSum) . ", Remaining amount: " . number_format($remainingAmount));
                    //                                         }
                    //                                     }
                    //                                 } else {
                    //                                     if ($get('debtor') == 0 && $get('creditor') == 0) {
                    //                                         $fail('Only one of these values can be zero.');
                    //                                     } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                    //                                         $fail('At least one of the values must be zero.');
                    //                                     }
                    //                                 }
                    //                             },
                    //                         ]),
                    //                     Forms\Components\Hidden::make('isCurrency'),
                    //                     Forms\Components\Hidden::make('currency_id')->default(defaultCurrency()?->id)->hidden(function (Get $get) {
                    //                         return $get('isCurrency');
                    //                     }),
                    //                     Section::make([
                    //                         Select::make('currency_id')->live()->label('Currency')->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
                    //                             Section::make([
                    //                                 TextInput::make('name')->required()->maxLength(255),
                    //                                 TextInput::make('symbol')->required()->maxLength(255),
                    //                                 TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    //                             ])->columns(3)
                    //                         ])->createOptionUsing(function ($data) {
                    //                             $data['company_id'] = getCompany()->id;
                    //                             Notification::make('success')->title('success')->success()->send();
                    //                             return Currency::query()->create($data)->getKey();
                    //                         })->editOptionForm([
                    //                             Section::make([
                    //                                 TextInput::make('name')->required()->maxLength(255),
                    //                                 TextInput::make('symbol')->required()->maxLength(255),
                    //                                 TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    //                             ])->columns(3)
                    //                         ])->afterStateUpdated(function ($state, Forms\Set $set) {
                    //                             $currency = Currency::query()->firstWhere('id', $state);
                    //                             if ($currency) {
                    //                                 $set('exchange_rate', $currency->exchange_rate);
                    //                             }
                    //                         })->editOptionAction(function ($state, Forms\Set $set) {
                    //                             $currency = Currency::query()->firstWhere('id', $state);
                    //                             if ($currency) {
                    //                                 $set('exchange_rate', $currency->exchange_rate);
                    //                             }
                    //                         }),
                    //                         TextInput::make('exchange_rate')->default(defaultCurrency()->exchange_rate)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    //                         Forms\Components\TextInput::make('debtor_foreign')
                    //                             ->readOnly()
                    //                             ->live(true)->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                    //                                 $set('debtor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',', '', $get('exchange_rate'))));
                    //                             })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                    //                                 fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    //                                     if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                    //                                         $fail('Only one of these values can be zero.');
                    //                                     } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                    //                                         $fail('At least one of the values must be zero.');
                    //                                     }
                    //                                 },
                    //                             ]),
                    //                         Forms\Components\TextInput::make('creditor_foreign')
                    //                             ->live(true)->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                    //                                 $set('creditor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',', '', $get('exchange_rate'))));
                    //                             })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                    //                                 fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    //                                     if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                    //                                         $fail('Only one of these values can be zero.');
                    //                                     } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                    //                                         $fail('At least one of the values must be zero.');
                    //                                     }
                    //                                 },
                    //                             ]),
                    //                     ])->columns(4)->visible(function (Get $get) {
                    //                         return $get('isCurrency');
                    //                     }),
                    //                     Forms\Components\Hidden::make('Cheque')->label('Cheque/Instalment')->live(),
                    //                     Forms\Components\Section::make([
                    //                         Forms\Components\Fieldset::make('cheque')->label('Cheque/Instalment')->relationship('cheque')->schema([
                    //                             Forms\Components\TextInput::make('cheque_number')->maxLength(255),
                    //                             Forms\Components\TextInput::make('amount')->default(function (Get $get) {
                    //                                 if ($get('debtor') > 0) {
                    //                                     return $get('debtor');
                    //                                 } else
                    //                                 if ($get('creditor') > 0) {
                    //                                     return $get('creditor');
                    //                                 } else {
                    //                                     return 0;
                    //                                 }
                    //                             })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                    //                             Forms\Components\DatePicker::make('issue_date')->required()->default(now()),
                    //                             Forms\Components\DatePicker::make('due_date')->required(),
                    //                             Forms\Components\TextInput::make('payer_name')->maxLength(255),
                    //                             Forms\Components\TextInput::make('payee_name')->maxLength(255),
                    //                             Forms\Components\TextInput::make('bank_name')->maxLength(255),
                    //                             Forms\Components\TextInput::make('branch_name')->maxLength(255),
                    //                             Forms\Components\Textarea::make('description')->columnSpanFull(),
                    //                             Forms\Components\ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                    //                             Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                    //                         ]),
                    //                     ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                    //                     Forms\Components\Hidden::make('financial_period_id')->required()->label('Financial Period')->default(getPeriod()?->id)
                    //                 ])->minItems(1)->columns(4)->defaultItems(1)
                    //                     ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                    //                         $data['user_id'] = auth()->id();
                    //                         $data['company_id'] = getCompany()->id;
                    //                         $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                    //                         return $data;
                    //                     })
                    //             ])->columnSpanFull()


                    //         ])->columnSpanFull(),

                    //     ])->columnSpanFull(),

                // ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('date_of_po', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('NO')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_orders_number')
                    ->label('PO No')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_po')
                    ->label('Date Of PO')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->state(fn($record) => number_format($record->items->map(fn($item) => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)))?->sum()).$record->currency->symbol)
                    ->searchable(),
                Tables\Columns\TextColumn::make('bid.total_cost')->alignCenter()->label('Total Final Price')->numeric(),

                // Tables\Columns\TextColumn::make('currency')
                // ->searchable(),
                // Tables\Columns\TextColumn::make('exchange_rate')
                // ->numeric()
                // ->sortable(),


                Tables\Columns\TextColumn::make('purchaseRequest.purchase_number')->badge()->url(fn($record) => PurchaseRequestResource::getUrl('index') . "?tableFilters[purchase_number][value]=" . $record->purchaseRequest?->id)
                    ->sortable()->label("PR No"),

                Tables\Columns\TextColumn::make('date_of_delivery')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\Textcolumn::make('status')->badge()
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
                SelectFilter::make('id')->searchable()->preload()->options(PurchaseOrder::where('company_id', getCompany()->id)->get()->pluck('purchase_orders_number', 'id'))
                    ->label("Po No"),
                SelectFilter::make('purchase_request_id')->searchable()->preload()->options(PurchaseRequest::where('company_id', getCompany()->id)->where('status','Approval')->get()->pluck('purchase_number', 'id'))
                    ->label("PR No"),
                SelectFilter::make('vendor_id')->searchable()->preload()->options(Parties::where('company_id', getCompany()->id)->where('account_code_vendor', '!=', null)->get()->pluck('name', 'id'))
                    ->label("Vendor"),
                DateRangeFilter::make('date_of_po'),

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
                Tables\Actions\Action::make('Invoice')->visible(fn($record) => $record->status==="Approved" and auth()->user()->can('invoice_purchase::order') and $record->invoice == null)->label('Invoice')->tooltip('Invoice')->icon('heroicon-o-credit-card')->iconSize(IconSize::Medium)->color('warning')->url(fn($record) => PurchaseOrderResource::getUrl('InvoicePurchaseOrder', ['record' => $record->id])),

                Tables\Actions\Action::make('prPDF')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.po', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->status == 'Approved'),
                Tables\Actions\Action::make('GRN')->label('GRN')->url(fn($record) => AssetResource::getUrl('create', ['po' => $record->id]))->visible(fn($record) => $record->items()->whereHas('product', function ($query) {
                        $query->where('product_type', 'unConsumable');
                    })->count() and $record->status === 'Approved')->hidden(fn($record) => $record->status === 'GRN And inventory' or $record->status === 'GRN'),
                //                Tables\Actions\DeleteAction::make()->visible(fn($record)=>$record->status==="pending" )
                Tables\Actions\Action::make('Inventory')->visible(fn($record) => $record->items()->whereHas('product', function ($query) {
                        $query->where('product_type', 'consumable');
                    })->count() and $record->status === 'Approved')->form(function ($record) {
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
                                    'quantity' => $item->quantity,
                                ];
                            })->toArray();

                            $table = '
        <table style="width:100%; border-collapse: collapse; border: 1px solid #ccc;">
            <thead style="background-color: #f0f0f0;">
                <tr>
                    <th style="padding: 8px; border: 1px solid #ccc;color: black !important">Product Title</th>
                    <th style="padding: 8px; border: 1px solid #ccc;color: black !important">Quantity</th>
                </tr>
            </thead>
            <tbody>';

                            foreach ($data as $row) {
                                $table .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ccc;text-align: center">' . $row['product'] . '</td>
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
                            Forms\Components\Select::make('package_id')->label('Package')->live()->searchable()->options(fn() => getCompany()->packages->mapWithKeys(function ($item) {
                                return [$item->id => $item->title . ' (' . $item->quantity . ')'];
                            }))->createOptionForm([
                                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                                Forms\Components\TextInput::make('quantity')->required()->numeric(),
                            ])->createOptionUsing(function ($data){
                                $record= Package::query()->create(['title'=>$data['title'],'quantity'=>$data['quantity'],'company_id'=>getCompany()->id]);
                                Notification::make('success')->success()->title('Submitted Successfully')->send();
                                return $record->getKey();
                            }),
                            TextInput::make('quantity')->numeric()->required(),
                            Forms\Components\Textarea::make('description')->columnSpanFull()->required(),
                        ])->columns(4)->formatStateUsing(function ($record) use ($products) {
                            $data = [];
                            foreach ($record->items->whereIn('product_id', array_keys($products->toArray())) as $item) {
                                $data[] = ['product_id' => $item->product_id, 'description' => $item->description, 'warehouse_id' => null, 'quantity' => null];
                            }
                            return $data;
                        })->reorderable(false)
                    ];
                })->action(function ($data, $record) {
                    $products = Product::query()
                        ->whereIn('id', function ($query) use ($record) {
                            return $query->select('product_id')
                                ->from('purchase_order_items')
                                ->where('purchase_order_id', $record->id);
                        })->where('product_type', 'consumable')
                        ->pluck('title', 'id');
                    $validateData = [];
                    foreach ($data['inventories'] as $inventory) {
                        $quantity=(int)$inventory['quantity'];
                        if (isset($inventory['package_id'])){
                            $package=Package::query()->firstWhere('id',$inventory['package_id']);
                            if ($package){
                                $quantity=$quantity*$package->quantity;
                            }
                        }
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
                        Stock::query()->create([
                            'inventory_id' => $inv->id,
                            'employee_id' => getEmployee()->id,
                            'quantity' => $inventory['quantity'],
                            'description' => $inventory['description'],
                            'type' => 1,
                            'purchase_order_id' => $record->id
                        ]);
                        $quantity=(int)$inventory['quantity'];
                        if (isset($inventory['package_id'])){
                            $package=Package::query()->firstWhere('id',$inventory['package_id']);
                            if ($package){
                                $quantity=$quantity*$package->quantity;
                            }
                        }
                        $inv->update(['quantity' => $inv->quantity + $quantity]);
                    }
                    if ($record->status === "GRN") {
                        $record->update(['status' => 'GRN And inventory']);
                    } else {
                        $record->update(['status' => 'Inventory']);
                    }

                    Notification::make('success')->success()->title('Successfully')->send();
                })->modalWidth(MaxWidth::SixExtraLarge)->hidden(fn($record) => $record->status === 'GRN And inventory' or $record->status === 'Inventory' or  $record->status === 'pending' or $record->status === 'rejected')

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
