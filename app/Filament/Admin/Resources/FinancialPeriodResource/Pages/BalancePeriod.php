<?php

namespace App\Filament\Admin\Resources\FinancialPeriodResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Models\Account;
use App\Models\Cheque;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Transaction;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class BalancePeriod extends ManageRelatedRecords
{
    protected static string $resource = FinancialPeriodResource::class;

    protected static string $relationship = 'transactions';
    protected static ?string $title = 'Initial Journal Entry';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Transactions';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')->default(1)->readOnly(),
                Forms\Components\DatePicker::make('date')->default(now())->required(),
                Forms\Components\Repeater::make('transactions')->deletable(false)->reorderable(false)->label('')->schema([
                    SelectTree::make('account_id')->formatStateUsing(function ($state, Forms\Set $set) {
                        $account = Cache::remember("account_with_currency_{$state}", 60, function () use ($state) {
                            return Account::query()
                                ->with(['currency'])
                                ->where('id', $state)
                                ->whereNot('currency_id', defaultCurrency()?->id)
                                ->first();
                        });

                        if ($account) {
                            $set('currency_id', $account->currency_id);
                            $set('exchange_rate', number_format($account->currency->exchange_rate));
                            $set('isCurrency', 1);
                            return $state;
                        }

                        $set('isCurrency', 0);
                        return $state;

                    })->afterStateUpdated(function ($state, Forms\Set $set) {
                        $account = Cache::remember("account_with_currency_{$state}", 60, function () use ($state) {
                            return Account::query()
                                ->with(['currency'])
                                ->where('id', $state)
                                ->whereNot('currency_id', defaultCurrency()?->id)
                                ->first();
                        });
                        if ($account) {
                            $set('currency_id', $account->currency_id);
                            $set('exchange_rate', number_format($account->currency->exchange_rate));
                            return $set('isCurrency', 1);
                        }
                        return $set('isCurrency', 0);
                    })->live(true)->defaultOpenLevel(3)->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->whereIn('group', ['Asset', 'Liabilitie', "Equity"])->where('company_id', getCompany()->id))->searchable(),
                    Forms\Components\TextInput::make('description')->default('Opening Journal Entry ')->required(),
                    Forms\Components\TextInput::make('debtor')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                        ->rules([
                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('debtor') > 0 && $get('creditor') > 0) {
                                    $fail('Only one of these values can be grander than zero.');
                                } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),
                    Forms\Components\TextInput::make('creditor')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                        ->rules([
                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('debtor') > 0 && $get('creditor') > 0) {
                                    $fail('Only one of these values can be grander zero.');
                                } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),
                    Forms\Components\Hidden::make('isCurrency'),
                    Forms\Components\Hidden::make('currency_id')->default(defaultCurrency()?->id)->hidden(function (Get $get) {
                        return $get('isCurrency');
                    }),
                    Section::make([
                        Select::make('currency_id')->live(true)->label('Currency')->required()->options(function (){
                           return  Cache::remember("currencies_for_company_" . getCompany()->id, 60, function () {
                               return Currency::query()
                                   ->where('company_id', getCompany()->id)
                                   ->pluck('name', 'id');
                           });
                        })->searchable()->preload()->createOptionForm([
                            Section::make([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('symbol')->required()->maxLength(255),
                                TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            ])->columns(3)
                        ])->afterStateUpdated(function ($state, Forms\Set $set) {
                            $currency = Currency::query()->firstWhere('id', $state);
                            if ($currency) {
                                $set('exchange_rate', $currency->exchange_rate);
                            }
                        }),
                        TextInput::make('exchange_rate')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        Forms\Components\TextInput::make('debtor_foreign')->default(0)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($get('debtor') > 0 && $get('creditor') > 0) {
                                    $fail('Only one of these values can be grander than zero.');
                                } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),
                        Forms\Components\TextInput::make('creditor_foreign')->default(0)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($get('debtor') > 0 && $get('creditor') > 0) {
                                    $fail('Only one of these values can be grander than zero.');
                                } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),
                    ])->columns(4)->visible(function (Get $get) {
                        return $get('isCurrency');
                    }),
                    Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                    Forms\Components\Section::make([
                        Forms\Components\Fieldset::make('cheque')->label('Cheque/Installment')->model(Transaction::class)->schema([
                            Forms\Components\TextInput::make('cheque_number')->maxLength(255),
                            Forms\Components\TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->numeric(),
                            Forms\Components\DatePicker::make('issue_date'),
                            Forms\Components\DatePicker::make('due_date'),
                            Forms\Components\TextInput::make('payer_name')->label('Payor Name')->maxLength(255),
                            Forms\Components\TextInput::make('payee_name')->maxLength(255),
                            Forms\Components\TextInput::make('bank_name')->maxLength(255),
                            Forms\Components\TextInput::make('branch_name')->maxLength(255),
                            Forms\Components\Textarea::make('description')->columnSpanFull(),
                            Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                        ]),
                    ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                ])->minItems(1)->columns(4)->defaultItems(1)->columnSpanFull()->formatStateUsing(function () {
                    $finance = FinancialPeriod::query()->with(['transactions','transactions.cheque'])->where('status', 'Before')->where('company_id', getCompany()->id)->first();
                    $children = Cache::get("accountsSelected");

                        $accountIds = array_unique($children);
                        $accounts = Account::query()
                            ->with('currency')
                            ->whereIn('id', $accountIds)
                            ->get()
                            ->keyBy('id'); // حالا با $accounts[$id] می‌تونی مستقیم بهش دسترسی داشته باشی

                        $array = [];

                        foreach ($accountIds as $child) {
                            $account = $accounts[$child];

                            $array[] = [
                                'account_id' => (int)$child,
                                'description' => "Opening Journal Entry " . $finance->name,
                                'currency_id' => $account->currency_id,
                                'exchange_rate' => $account->currency->exchange_rate,
                                'isCurrency' => $account->currency_id != defaultCurrency()->id ? 1 : 0,
                                'Cheque' => (bool)$account->has_cheque,
                                'debtor' => 0,
                                'creditor' => 0,
                                'debtor_foreign' => 0,
                                'creditor_foreign' => 0,
                            ];
                        }

                        return $array;


                })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('account.name')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('account.code')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('description')->alignCenter(),
                Tables\Columns\TextColumn::make('debtor')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('creditor')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('currency.name')->alignCenter()->numeric(),
                Tables\Columns\TextColumn::make('debtor_foreign')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('creditor_foreign')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('exchange_rate')->alignCenter()->numeric(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('start')
                    ->requiresConfirmation()
                    ->label('Start Fiscal Year')->action(function ($record) {

                        if (isset($this->record->transactions[0])) {
                            $debtor = $this->record->transactions->sum('debtor');
                            $creditor = $this->record->transactions->sum('creditor');
                            $equity = $debtor - $creditor;
                            if (!defaultCurrency()?->id) {
                                Notification::make('error')->warning()->title('Currency is not defined')->send();
                                return;
                            }
                            if ($equity !== 0) {
                                $this->record->transactions()->create([
                                    'account_id' => Account::query()->where('stamp', 'Equity')->where('company_id', getCompany()->id)->first()->id,
                                    'creditor' => $equity >= 0 ? $equity : 0,
                                    'debtor' => $equity <= 0 ? abs($equity) : 0,
                                    'exchange_rate' => defaultCurrency()->id,
                                    'debtor_foreign' => 0,
                                    'creditor_foreign' => 0,
                                    'description' => 'auto generate equity',
                                    'company_id' => getCompany()->id,
                                    'user_id' => auth()->user()->id,
                                    'invoice_id' => $this->record->transactions[0]->invoice_id,
                                    'financial_period_id' => $this->record->id,
                                    'currency_id' => defaultCurrency()?->id
                                ]);
                            }
                        }

                        $this->record->update(['status' => 'During']);
                        $url = "admin/" . getCompany()->id . "/finance-settings/financial-periods";
                        return redirect($url);
                    }),
                Tables\Actions\Action::make('filter')->color('purple')->label('Select Accounts to Opening Balance')->form([
                    Select::make('accounts')
                        ->label('Accounts')
                        ->options(function () {
                            $financialPeriodId = $this->record->id;

                            $accounts = \App\Models\Account::query()
                                ->whereIn('stamp', ['Assets', 'Liabilities', 'Equity'])
                                ->with([
                                    'currency',
                                    'transactions' => fn($q) => $q->where('financial_period_id', $financialPeriodId),
                                    'childerns.transactions' => fn($q) => $q->where('financial_period_id', $financialPeriodId),
                                    'childerns.childerns.transactions' => fn($q) => $q->where('financial_period_id', $financialPeriodId),
                                    'childerns.childerns.childerns.transactions' => fn($q) => $q->where('financial_period_id', $financialPeriodId),
                                    'childerns',
                                    'childerns.childerns',
                                    'childerns.childerns.childerns',
                                ])
                                ->where('company_id', getCompany()->id)
                                ->get();

                            $grouped = [];

                            foreach ($accounts as $account) {
                                if (isset($account->childerns[0])) {
                                    foreach ($account->childerns->where('hidden', 0) as $childLevel1) {
                                        if (isset($childLevel1->childerns[0])) {
                                            foreach ($childLevel1->childerns->where('hidden', 0) as $childLevel2) {
                                                if (isset($childLevel2->childerns[0])) {
                                                    foreach ($childLevel2->childerns->where('hidden', 0) as $leaf) {
                                                        if ($leaf->transactions->isEmpty()) {
                                                            $grouped[$childLevel2->title][$leaf->id] = $leaf->title;
                                                        }
                                                    }
                                                } else {
                                                    if ($childLevel2->transactions->isEmpty()) {
                                                        $grouped[$childLevel1->title][$childLevel2->id] = $childLevel2->title;
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($childLevel1->transactions->isEmpty()) {
                                                $grouped[$account->title][$childLevel1->id] = $childLevel1->title;
                                            }
                                        }
                                    }
                                } else {
                                    if ($account->transactions->isEmpty()) {
                                        $grouped['Root'][$account->id] = $account->title;
                                    }
                                }
                            }

                            return $grouped;


                        })
                        ->searchable()
                        ->multiple()
                        ->maxItems(10)->required()

                ])->action(function ($data){
                    Cache::put("accountsSelected",$data['accounts']);
                    sendSuccessNotification();
                }),
                Tables\Actions\CreateAction::make("create")->color("success")->slideOver()->stickyModalHeader(false)->label(function () {
                        return "New Record";
                })->closeModalByClickingAway(false)->modalWidth(MaxWidth::Full)->action(function ($data) {
                    $finance = FinancialPeriod::query()->where('status', 'Before')->where('company_id', getCompany()->id)->first();

                    $title = "Opening Journal Entry " . $finance->name;
                    $invoice = Invoice::query()->firstWhere('name', $title);

                    // dd($invoice);
                    if ($invoice) {

                        $invoice->update(['date' => $data['date']]);


                        foreach ($data['transactions'] as $transaction) {
                            // dd($transaction['isCurrency']==0);

                            if ($transaction['isCurrency'] == 0) {
                                $transaction["currency_id"] = defaultCurrency()?->id;
                                $transaction["exchange_rate"] = defaultCurrency()?->exchange_rate;
                                $transaction["debtor_foreign"] = 0;
                                $transaction["creditor_foreign"] = 0;
                            }

                            if ($transaction['debtor'] > 0 or $transaction['creditor'] > 0) {
                                $transaction['financial_period_id'] = $finance->id;
                                $transaction['invoice_id'] = $invoice->id;
                                $transaction['company_id'] = getCompany()->id;
                                $transaction['user_id'] = auth()->id();

                                if (defaultCurrency()?->id) {
                                    if ($transaction['isCurrency'] == 0) {
                                        $transaction['currency_id'] = defaultCurrency()?->id;
                                    }
                                } else {
                                    Notification::make('error')->warning()->title('Currency is not defined')->send();
                                    return;
                                }

                                // dd($transaction);
                                $record = Transaction::query()->create($transaction);
                                if ($transaction['Cheque']  and isset($transaction['amount'])) {
                                    $transaction['company_id'] = getCompany()->id;
                                    $transaction['amount'] = str_replace(',', '', $transaction['amount']);
                                    $transaction['transaction_id'] = $record->id;
                                    $type = str_replace(',', '', $transaction['debtor']) > 0 ? 0 : 1;
                                    Cheque::query()->create([
                                        'type' => $type,
                                        "bank_name" => $transaction['bank_name'],
                                        "branch_name" => $transaction['branch_name'],
                                        "amount" => $transaction['amount'],
                                        "issue_date" => $transaction['issue_date'],
                                        "due_date" => $transaction['due_date'],
                                        "payer_name" => $transaction['payer_name'],
                                        "payee_name" => $transaction['payee_name'],
                                        "description" => $transaction['description'],
                                        "company_id" => $transaction['company_id'],
                                        "cheque_number" => $transaction['cheque_number'],
                                        'transaction_id' => $record->id
                                    ]);
                                }
                            }
                        }
                    } else {
                        $invoice = Invoice::query()->create(['name' => $title, 'number' => 1, 'date' => $data['date'], 'description' => null, 'reference' => null, 'company_id' => getCompany()->id, 'document' => null]);

                        if ($invoice) {
                            // dd($data['transactions']);
                            foreach ($data['transactions'] as $transaction) {
                                if ($transaction['isCurrency'] == 0) {
                                    $transaction["currency_id"] = defaultCurrency()?->id;
                                    $transaction["exchange_rate"] = defaultCurrency()?->exchange_rate;
                                    $transaction["debtor_foreign"] = 0;
                                    $transaction["creditor_foreign"] = 0;
                                }

                                if ($transaction['debtor'] > 0 or $transaction['creditor'] > 0) {
                                    $transaction['financial_period_id'] = $finance->id;
                                    $transaction['invoice_id'] = $invoice->id;
                                    $transaction['company_id'] = getCompany()->id;
                                    $transaction['user_id'] = auth()->id();
                                    if (defaultCurrency()?->id) {
                                        if ($transaction['isCurrency'] == 0) {
                                            $transaction['currency_id'] = defaultCurrency()?->id;
                                        }
                                    } else {
                                        Notification::make('error')->warning()->title('Currency is not defined')->send();
                                        return;
                                    }

                                    $record = Transaction::query()->create($transaction);
                                    if ($transaction['Cheque'] and isset($transaction['amount']) ) {
                                        $transaction['company_id'] = getCompany()->id;
                                        $transaction['amount'] = str_replace(',', '', $transaction['amount']);
                                        $transaction['transaction_id'] = $record->id;
                                        $type = str_replace(',', '', $transaction['debtor']) > 0 ? 0 : 1;
                                        Cheque::query()->create([
                                            'type' => $type,
                                            "bank_name" => $transaction['bank_name'],
                                            "branch_name" => $transaction['branch_name'],
                                            "amount" => $transaction['amount'],
                                            "issue_date" => $transaction['issue_date'],
                                            "due_date" => $transaction['due_date'],
                                            "payer_name" => $transaction['payer_name'],
                                            "payee_name" => $transaction['payee_name'],
                                            "description" => $transaction['description'],
                                            "company_id" => $transaction['company_id'],
                                            "cheque_number" => $transaction['cheque_number'],
                                            'transaction_id' => $record->id
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }),
                Tables\Actions\CreateAction::make('edit')->color("warning")->slideOver()->stickyModalHeader(false)->label(function () {
                        return "Edit Record";
                })->disabled(function () {
                    $finance = FinancialPeriod::query()->where('status', 'Before')->where('company_id', getCompany()->id)->first();
                    if (isset($finance->transactions[0])) {
                        return false;
                    } else {
                        return true;
                    }
                })->form([
                        Forms\Components\TextInput::make('number')->default(1)->readOnly(),
                        Forms\Components\DatePicker::make('date')->default(now())->required(),
                        Forms\Components\Repeater::make('transactions')->deletable(false)->reorderable(false)->label('')->schema([
                            SelectTree::make('account_id')->formatStateUsing(function ($state, Forms\Set $set) {
                                $account = Cache::remember("account_with_currency_{$state}", 60, function () use ($state) {
                                    return Account::query()
                                        ->with(['currency'])
                                        ->where('id', $state)
                                        ->whereNot('currency_id', defaultCurrency()?->id)
                                        ->first();
                                });

                                if ($account) {
                                    $set('currency_id', $account->currency_id);
                                    $set('exchange_rate', number_format($account->currency->exchange_rate));
                                    $set('isCurrency', 1);
                                    return $state;
                                }

                                $set('isCurrency', 0);
                                return $state;

                            })->afterStateUpdated(function ($state, Forms\Set $set) {
                                $account = Cache::remember("account_with_currency_{$state}", 60, function () use ($state) {
                                    return Account::query()
                                        ->with(['currency'])
                                        ->where('id', $state)
                                        ->whereNot('currency_id', defaultCurrency()?->id)
                                        ->first();
                                });
                                if ($account) {
                                    $set('currency_id', $account->currency_id);
                                    $set('exchange_rate', number_format($account->currency->exchange_rate));
                                    return $set('isCurrency', 1);
                                }
                                return $set('isCurrency', 0);
                            })->live(true)->defaultOpenLevel(3)->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->whereIn('group', ['Asset', 'Liabilitie', "Equity"])->where('company_id', getCompany()->id))->searchable(),
                            Forms\Components\TextInput::make('description')->default('Opening Journal Entry ')->required(),
                            Forms\Components\TextInput::make('debtor')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                                ->rules([
                                    fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if ($get('debtor') > 0 && $get('creditor') > 0) {
                                            $fail('Only one of these values can be grander than zero.');
                                        } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    },
                                ]),
                            Forms\Components\TextInput::make('creditor')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                                ->rules([
                                    fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if ($get('debtor') > 0 && $get('creditor') > 0) {
                                            $fail('Only one of these values can be grander zero.');
                                        } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    },
                                ]),
                            Forms\Components\Hidden::make('isCurrency'),
                            Forms\Components\Hidden::make('currency_id')->default(defaultCurrency()?->id)->hidden(function (Get $get) {
                                return $get('isCurrency');
                            }),
                            Section::make([
                                Select::make('currency_id')->live(true)->label('Currency')->required()->options(function (){
                                    return  Cache::remember("currencies_for_company_" . getCompany()->id, 60, function () {
                                        return Currency::query()
                                            ->where('company_id', getCompany()->id)
                                            ->pluck('name', 'id');
                                    });
                                })->searchable()->preload()->createOptionForm([
                                    Section::make([
                                        TextInput::make('name')->required()->maxLength(255),
                                        TextInput::make('symbol')->required()->maxLength(255),
                                        TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    ])->columns(3)
                                ])->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $currency = Currency::query()->firstWhere('id', $state);
                                    if ($currency) {
                                        $set('exchange_rate', $currency->exchange_rate);
                                    }
                                }),
                                TextInput::make('exchange_rate')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                Forms\Components\TextInput::make('debtor_foreign')->default(0)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                        if ($get('debtor') > 0 && $get('creditor') > 0) {
                                            $fail('Only one of these values can be grander than zero.');
                                        } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    },
                                ]),
                                Forms\Components\TextInput::make('creditor_foreign')->default(0)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                        if ($get('debtor') > 0 && $get('creditor') > 0) {
                                            $fail('Only one of these values can be grander than zero.');
                                        } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    },
                                ]),
                            ])->columns(4)->visible(function (Get $get) {
                                return $get('isCurrency');
                            }),
                            Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                            Forms\Components\Section::make([
                                Forms\Components\Fieldset::make('cheque')->label('Cheque/Installment')->model(Transaction::class)->schema([
                                    Forms\Components\TextInput::make('cheque_number')->maxLength(255),
                                    Forms\Components\TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->numeric(),
                                    Forms\Components\DatePicker::make('issue_date'),
                                    Forms\Components\DatePicker::make('due_date'),
                                    Forms\Components\TextInput::make('payer_name')->label('Payor Name')->maxLength(255),
                                    Forms\Components\TextInput::make('payee_name')->maxLength(255),
                                    Forms\Components\TextInput::make('bank_name')->maxLength(255),
                                    Forms\Components\TextInput::make('branch_name')->maxLength(255),
                                    Forms\Components\Textarea::make('description')->columnSpanFull(),
                                    Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                                ]),
                            ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                        ])->minItems(1)->columns(4)->defaultItems(1)->columnSpanFull()->formatStateUsing(function () {
                            $finance = FinancialPeriod::query()->with(['transactions','transactions.cheque'])->where('status', 'Before')->where('company_id', getCompany()->id)->first();
                            $children = Cache::remember("account_children_ids_company_" . getCompany()->id, 60, function () {
                                $children = [];

                                $accounts = Account::query()
                                    ->whereIn('stamp', ['Assets', 'Liabilities', 'Equity'])
                                    ->with(['childerns', 'childerns.childerns', 'childerns.childerns.childerns', 'currency'])
                                    ->where('company_id', getCompany()->id)
                                    ->get();

                                foreach ($accounts as $account) {
                                    if (isset($account->childerns[0])) {
                                        foreach ($account->childerns->where('hidden', 0) as $childern) {
                                            if (isset($childern->childerns[0])) {
                                                foreach ($childern->childerns->where('hidden', 0) as $child) {
                                                    if (isset($child->childerns[0])) {
                                                        foreach ($child->childerns->where('hidden', 0) as $item) {
                                                            $children[] = $item->id;
                                                        }
                                                    } else {
                                                        $children[] = $child->id;
                                                    }
                                                }
                                            } else {
                                                $children[] = $childern->id;
                                            }
                                        }
                                    } else {
                                        $children[] = $account->id;
                                    }
                                }

                                return $children;
                            });

                            if (isset($finance->transactions[0])) {
                                $arrayData = [];
                                $arr = [];
                                foreach ($finance->transactions()->with('cheque')->get()->toArray() as $value) {
                                    // dd($value,defaultCurrency()->id);
                                    if ($value['currency_id'] != defaultCurrency()->id) {
                                        $value['isCurrency'] = 1;
                                    } else {
                                        $value['isCurrency'] = 0;
                                    }
                                    if (isset($value['cheque']['id'])) {
                                        $value['Cheque'] = true;
                                        $arrayData[] = [...$value, ...$value['cheque']];
                                    } else {
                                        $arrayData[] = $value;
                                    }
                                }
                                // dd($arrayData);
                                return $arrayData;
                            } else {
                                $accountIds = array_unique($children);

                                $accounts = Account::query()
                                    ->with('currency')
                                    ->whereIn('id', $accountIds)
                                    ->get()
                                    ->keyBy('id'); // حالا با $accounts[$id] می‌تونی مستقیم بهش دسترسی داشته باشی

                                $array = [];

                                foreach ($accountIds as $child) {
                                    $account = $accounts[$child];
                                    $array[] = [
                                        'account_id' => $child,
                                        'description' => "Opening Journal Entry " . $finance->name,
                                        'currency_id' => $account->currency_id,
                                        'exchange_rate' => $account->currency->exchange_rate,
                                        'isCurrency' => $account->currency_id != defaultCurrency()->id ? 1 : 0,
                                        'Cheque' => (bool)$account->has_cheque,
                                        'debtor' => 0,
                                        'creditor' => 0,
                                        'debtor_foreign' => 0,
                                        'creditor_foreign' => 0,
                                    ];
                                }

                                return $array;

                            }
                        })
                ])->closeModalByClickingAway(false)->modalWidth(MaxWidth::Full)->action(function ($data) {
                    $finance = FinancialPeriod::query()->where('status', 'Before')->where('company_id', getCompany()->id)->first();

                    $title = "Opening Journal Entry " . $finance->name;
                    $invoice = Invoice::query()->firstWhere('name', $title);

                    // dd($invoice);
                    if ($invoice) {
                        $invoice->update(['date' => $data['date']]);
                        foreach ($data['transactions'] as $transaction) {

                            if ($transaction['isCurrency'] == 0) {
                                $transaction["currency_id"] = defaultCurrency()?->id;
                                $transaction["exchange_rate"] = defaultCurrency()?->exchange_rate;
                                $transaction["debtor_foreign"] = 0;
                                $transaction["creditor_foreign"] = 0;
                            }

                            if ($transaction['debtor'] > 0 or $transaction['creditor'] > 0) {
                                $transaction['financial_period_id'] = $finance->id;
                                $transaction['invoice_id'] = $invoice->id;
                                $transaction['company_id'] = getCompany()->id;
                                $transaction['user_id'] = auth()->id();

                                if (defaultCurrency()?->id) {
                                    if ($transaction['isCurrency'] == 0) {
                                        $transaction['currency_id'] = defaultCurrency()?->id;
                                    }
                                } else {
                                    Notification::make('error')->warning()->title('Currency is not defined')->send();
                                    return;
                                }

                                // dd($transaction);

                                $record = Transaction::query()->create($transaction);
                                if ($transaction['Cheque']  and isset($transaction['amount'])) {

                                    $transaction['company_id'] = getCompany()->id;
                                    $transaction['amount'] = str_replace(',', '', $transaction['amount']);
                                    $transaction['transaction_id'] = $record->id;
                                    $type = str_replace(',', '', $transaction['debtor']) > 0 ? 0 : 1;
                                    Cheque::query()->create([
                                        'type' => $type,
                                        "bank_name" => $transaction['bank_name'],
                                        "branch_name" => $transaction['branch_name'],
                                        "amount" => $transaction['amount'],
                                        "issue_date" => $transaction['issue_date'],
                                        "due_date" => $transaction['due_date'],
                                        "payer_name" => $transaction['payer_name'],
                                        "payee_name" => $transaction['payee_name'],
                                        "description" => $transaction['description'],
                                        "company_id" => $transaction['company_id'],
                                        "cheque_number" => $transaction['cheque_number'],
                                        'transaction_id' => $record->id
                                    ]);
                                }
                            }
                        }
                    }
                })
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                //                Tables\Actions\EditAction::make()->form([
                //
                //                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //                    Tables\Actions\DissociateBulkAction::make(),
                    //                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
