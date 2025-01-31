<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Account;
use App\Models\FinancialPeriod;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Wizard::make([
                    Wizard\Step::make('Order')
                        ->schema([
                            Section::make('Payment')->schema([

                                Forms\Components\Select::make('vendor_id')->label('Vendor')

                                    ->options((getCompany()->parties->where('type', 'vendor')->pluck('info', 'id')))

                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('currency')
                                    ->default(getCompany()->currency)
                                    ->required()->required()->options(getCurrency())->searchable()->preload(),
                                Forms\Components\TextInput::make('exchange_rate')
                                    ->required()->default(1)
                                    ->numeric(),
                            ])->columns(3),
                            Section::make('Request')->schema([
                                Forms\Components\Select::make('prepared_by')->live()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->options(getCompany()->employees->pluck('fullName', 'id'))
                                    ->default(fn() => auth()->user()->employee->id),

                                Forms\Components\TextInput::make('purchase_orders_number')->default(function () {
                                    $puncher = PurchaseOrder::query()->where('company_id', getCompany()->id)->latest()->first();
                                    if ($puncher) {
                                        return  generateNextCodePO($puncher->purchase_orders_number);
                                    } else {
                                        return "0001";
                                    }
                                })->label('PO NO')
                                    ->required()
                                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                                        return $rule->where('company_id', getCompany()->id);
                                    })
                                    ->maxLength(50),



                                Forms\Components\DatePicker::make('date_of_delivery')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\TextInput::make('location_of_delivery')
                                    ->maxLength(255),



                                Forms\Components\DatePicker::make('date_of_po')->default(now())
                                    ->label('Date of PO')
                                    ->required(),
                                // Forms\Components\Select::make('bid_id')
                                // ->relationship('bid', 'id')
                                // ->required(),
                                // Forms\Components\Select::make('quotation_id')
                                // ->relationship('quotation', 'id')
                                // ->required(),
                                Forms\Components\Select::make('purchase_request_id')->live()
                                    ->label('PR NO')
                                    ->searchable()
                                    ->preload()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $record = PurchaseRequest::query()->with('bid')->firstWhere('id', $state);
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
                                            } else {
                                                $set('RequestedItems', $record->items->where('status', 'approve')->toArray());
                                            }
                                        }
                                    })
                                    ->options(getCompany()->purchaseRequests->pluck('purchase_number', 'id')),


                                Forms\Components\Hidden::make('company_id')
                                    ->default(getCompany()->id)
                                    ->required(),



                                Repeater::make('RequestedItems')->defaultItems(0)->required()->relationship('items')
                                    // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Product')->options(function ($state) {
                                                $products = getCompany()->products->where('id', $state);
                                                $data = [];
                                                foreach ($products as $product) {
                                                    $data[$product->id] = $product->info;
                                                }
                                                return $data;
                                            })->required()->searchable()->preload(),
                                        Forms\Components\TextInput::make('description')->label('Description')->required(),
                                        Forms\Components\Select::make('unit_id')->required()->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id')),
                                        Forms\Components\TextInput::make('quantity')->required()->live(true),
                                        Forms\Components\TextInput::make('unit_price')->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $freights = $get('taxes') === null ? 0 : (float) $get('taxes');
                                            $q = $get('quantity');
                                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                            $price = $state !== null ? str_replace(',', '', $state) : 0;
                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        })->live(true)
                                            // ->readOnly(fn(Get $get)=>$record=PurchaseRequest::query()->with('bid')->firstWhere('id',$state);)
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')->label('Final Price'),
                                        Forms\Components\TextInput::make('taxes')->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                            $q = $get('quantity');
                                            $tax = $state === null ? 0 : (float)$state;
                                            $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
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
                                        Forms\Components\TextInput::make('freights')->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $freights = $state === null ? 0 : (float) $state;
                                            $q = $get('quantity');
                                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                            $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        })->live(true)
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(','),

                                        Forms\Components\Select::make('project_id')
                                            ->searchable()
                                            ->preload()
                                            ->disabled()
                                            ->label('Project')
                                            ->options(getCompany()->projects->pluck('name', 'id')),

                                        TextInput::make('total')->readOnly(),

                                    ])
                                    ->columns(9)
                                    ->columnSpanFull(),
                            ])->columns(3)
                        ]),
                    Wizard\Step::make('Delivery')
                        ->schema([
                            Fieldset::make('invoice')->relationship('invoice')->schema([

                                Forms\Components\Hidden::make('company_id')->default(Filament::getTenant()->id)->required(),

                                Forms\Components\Section::make([
                                    Forms\Components\TextInput::make('number')
                                        ->columnSpan(1)
                                        ->default(getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1)->label('Voucher Number')->required()->maxLength(255)->readOnly(),
                                    Forms\Components\TextInput::make('name')
                                        ->columnSpan(3)
                                        ->label('Voucher Title')->required()->maxLength(255),
                                    Forms\Components\TextInput::make('reference')
                                        ->columnSpan(1)
                                        ->maxLength(255),
                                    Forms\Components\DateTimePicker::make('date')
                                        ->columnSpan(2)
                                        ->required()->default(now()),
                                    Forms\Components\FileUpload::make('document')->placeholder('Browse')->extraInputAttributes(['style' => 'height:30px!important;'])
                                        ->nullable(),
                                ])->columns(8),

                                Forms\Components\Section::make([
                                    Forms\Components\Repeater::make('transactions')->label('')->relationship('transactions')->schema([
                                        SelectTree::make('account_id')->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id))->searchable(),
                                        Forms\Components\TextInput::make('description')->required(),


                                        Forms\Components\TextInput::make('creditor')
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                $set('cheque.amount', $state);
                                            })
                                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                            ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                                            ->rules([
                                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                                                    if ($get('creditor') == 0) {
                                                        $fail('The creditor field must be not zero.');
                                                    } else {

                                                        // dd(()));
                                                        $produtTotal = array_map(function ($item) {
                                                            // dd($item);
                                                            return (($item['quantity'] * str_replace(',','',$item['unit_price'])) + (($item['quantity'] * str_replace(',','',$item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',','',$item['unit_price']) * $item['freights']) / 100));
                                                        }, $get->getData()['RequestedItems']);

                                                        $invoiceTotal = array_map(function ($item) {
                                                            // dd($item);
                                                            return (str_replace(',','',$item['creditor']));
                                                        }, $get->getData()['invoice']['transactions']);

                                                        $productSum = collect($produtTotal)->sum(); 
                                                        $invoiceSum = collect($invoiceTotal)->sum(); 

                                                        if ($invoiceSum != $productSum) {
                                                            $remainingAmount = $productSum - $invoiceSum;
                                                            $fail("The paid amount does not match the total price. Total amount:". number_format($productSum).", Remaining amount: ".number_format($remainingAmount));
                                                        }
                                                    }
                                                },
                                            ]),
                                        Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                                        Forms\Components\Section::make([
                                            Forms\Components\Fieldset::make('cheque')->relationship('cheque')->schema([
                                                Forms\Components\TextInput::make('cheque_number')->required()->maxLength(255),
                                                Forms\Components\TextInput::make('amount')->default(function (Get $get) {

                                                    if ($get('debtor') > 0) {
                                                        return $get('debtor');
                                                    }
                                                    if ($get('creditor') > 0) {
                                                        return $get('creditor');
                                                    }
                                                })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                                                Forms\Components\DatePicker::make('issue_date')->required(),
                                                Forms\Components\DatePicker::make('due_date')->required(),
                                                Forms\Components\TextInput::make('payer_name')->required()->maxLength(255),
                                                Forms\Components\TextInput::make('payee_name')->required()->maxLength(255),
                                                Forms\Components\TextInput::make('bank_name')->maxLength(255),
                                                Forms\Components\TextInput::make('branch_name')->maxLength(255),
                                                Forms\Components\Textarea::make('description')->columnSpanFull(),
                                                Forms\Components\ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                                                Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                                            ]),
                                        ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                                        Forms\Components\Hidden::make('financial_period_id')->required()->label('Financial Period')
                                            ->default(FinancialPeriod::query()->where('company_id', getCompany()->id)->firstWhere('status', "During")?->id)
                                    ])->minItems(2)->columns(5)->defaultItems(2)
                                        ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                                            $data['user_id'] = auth()->id();
                                            $data['company_id'] = getCompany()->id;
                                            $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                                            return $data;
                                        })
                                ])->columns(1)->columnSpanFull()

                            ])
                        ]),

                ])->columnSpanFull(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.name'),
                Tables\Columns\TextColumn::make('date_of_delivery')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')
                    ->searchable(),
                Tables\Columns\TextColumn::make('po_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_po')
                    ->label('Date Of PO')
                    ->date()
                    ->sortable(),
                Tables\Columns\Textcolumn::make('status')
                    ->label('Status'),


                Tables\Columns\TextColumn::make('purchase_request_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('registration')->label('Registration assets')->url(fn($record) => AssetResource::getUrl('create', ['po' => $record->id]))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
