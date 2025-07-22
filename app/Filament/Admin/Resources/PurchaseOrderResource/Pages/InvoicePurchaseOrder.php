<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Account;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Facades\FilamentView;
use Filament\Support\RawJs;

class   InvoicePurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;


    protected function authorizeAccess(): void
    {
//        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }


    public function afterFill()
    {
        $transactions = [];

        $financial = getPeriod()->id;
        $company = getPeriod()->company_id;
        foreach ($this->record->items as $item) {
            $ex = $item->currency_id != defaultCurrency()->id;
            if ($item->vendor?->account_vendor) {
                $transactions[] = [
                    'account_id' => $item->vendor->account_vendor,
                    'description' => $item->description,
                    'creditor' => number_format($item->total, 2),
                    'debtor' => 0,
                    'Cheque' => $item->vendor->accountVendor->has_cheque,
                    'need_cheque' => $item->vendor->accountVendor->has_cheque,
                    'isCurrency' => $ex ? 1 : 0,
                    'currency_id' => $item->currency_id,
                    'exchange_rate' => $item->exchange_rate,
                    'creditor_foreign' => number_format($item->total / $item->exchange_rate, 2),
                    'debtor_foreign' => 0,
                    'financial_period_id' => $financial,
                    'company_id' => $company,
                    'cheque' => [
                        'issue_date' => now(),
                        'amount' => $ex ? number_format($item->total, 2) : null,
                        'type' => 1,
                        'payee_name' => $ex ? $item->vendor->name : null,
                        'company_id' => $company
                    ]

                ];
            }

            $transactions[] = [
                'account_id' => $item->product->sub_account_id,
                'description' => $item->description,
                'creditor' => 0,
                'debtor' => number_format($item->total, 2),
                'Cheque' => 0,
                'isCurrency' => $ex ? 1 : 0,
                'currency_id' => $item->currency_id,
                'exchange_rate' => $item->exchange_rate,
                'creditor_foreign' => 0,
                'debtor_foreign' => number_format($item->total / $item->exchange_rate, 2),
                'financial_period_id' => $financial,
                'company_id' => $company

            ];
        }
        $this->data['invoice']['name'] = "Paid PO " . $this->record->purchase_orders_number;
        $this->data['invoice']['reference'] = " PO No " . $this->record->purchase_orders_number;
        $this->data['invoice']['transactions'] = $transactions;


    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        // dd($this->record);
        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fieldset::make('invoice')->relationship('invoice')->schema([

                Hidden::make('company_id')->default(getCompany()->id)->required(),
                Section::make([
                    TextInput::make('number')->columnSpan(1)->default(getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1)->label('Voucher Number')->required()->maxLength(255)->readOnly(),
                    TextInput::make('name')->columnSpan(3)->label('Voucher Title')->required()->maxLength(255),
                    TextInput::make('reference')->columnSpan(1)->maxLength(255),
                    DatePicker::make('date')->columnSpan(2)->required()->default(now()),
                    FileUpload::make('document')->placeholder('Browse')->extraInputAttributes(['style' => 'height:30px!important;'])->nullable(),
                    Placeholder::make('total :')->live()->content(function (Get $get) {
                        if ($this->record->items->toArray()) {

                            return number_format($this->record->items->sum('total'), 2);
                        }
                    })->inlineLabel()
                ])->columns(8),

                Section::make([
                    Repeater::make('transactions')->label('')->relationship('transactions')->schema([
                        Hidden::make('company_id')->default(getCompany()->id)->required(),

                        SelectTree::make('account_id')->formatStateUsing(function ($state, Set $set) {
                            $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                            if ($account) {
                                $set('currency_id', $account->currency_id);
                                $set('exchange_rate', number_format($account->currency->exchange_rate));
                                $set('isCurrency', 1);
                                return $state;
                            }
                            $set('isCurrency', 0);
                            return $state;
                        })->afterStateUpdated(function ($state, Set $set, $get) {
                            $query = Account::query()->find($state);
                            // dd($query);
                            if ($query) {
                                if ($query->type == 'debtor') {
                                    $set('cheque.type', 0);
                                } else {
                                    $set('cheque.type', 1);
                                }
                                if ($query->has_cheque == 1) {
                                    $set('Cheque', true);
//                                    $set('need_cheque', true);
                                } else {
                                    $set('Cheque', false);
//                                    $set('need_cheque', false);
                                }


                            } else {
                                $set('Cheque', false);
                            }
                            $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                            // dd($account);
                            if ($account) {
                                $set('currency_id', $account->currency_id);
                                $set('exchange_rate', number_format($account->currency->exchange_rate));
                                return $set('isCurrency', 1);
                            }
                            return $set('isCurrency', 0);
                        })->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id))->searchable(),
                        TextInput::make('description')->required(),

                        TextInput::make('debtor')->prefix(defaultCurrency()->name)->live(true)->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if ($get('Cheque')) {
                                if ($state >= $get('creditor')) {
                                    $set('cheque.amount', $state);
                                } else {
                                    $set('cheque.amount', $get('creditor'));
                                }
                            }
                        })->mask(RawJs::make('$money($input)'))->readOnly(function (Get $get) {
                            return $get('isCurrency');
                        })->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($get('debtor') == 0 && $get('creditor') == 0) {
                                    $fail('Only one of these values can be zero.');
                                } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),
                        TextInput::make('creditor')->prefix(defaultCurrency()->name)->readOnly(function (Get $get) {
                            return $get('isCurrency');
                        })->live(true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($get('Cheque')) {
                                    if ($state >= $get('debtor')) {
                                        $set('cheque.amount', $state);
                                    } else {
                                        $set('cheque.amount', $get('debtor'));
                                    }
                                }
                            })
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                            ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                            ->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if ($get('debtor') == 0 && $get('creditor') == 0) {
                                        $fail('Only one of these values can be zero.');
                                    } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                        $fail('At least one of the values must be zero.');
                                    }
                                },
                            ]),
                        Hidden::make('isCurrency'),
                        Hidden::make('currency_id')->default(defaultCurrency()?->id)->hidden(function (Get $get) {
                            return $get('isCurrency');
                        }),
                        Section::make([
                            Select::make('currency_id')->live()->label('Currency')->required()->relationship('currency', 'name', modifyQueryUsing: fn($query, $state) => $query->where('id', $state))->searchable()->preload()->createOptionForm([
                                Section::make([
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
                            ])->afterStateUpdated(function ($state, Set $set) {
                                $currency = Currency::query()->firstWhere('id', $state);
                                if ($currency) {
                                    $set('exchange_rate', $currency->exchange_rate);
                                }
                            })->editOptionAction(function ($state, Set $set) {
                                $currency = Currency::query()->firstWhere('id', $state);
                                if ($currency) {
                                    $set('exchange_rate', $currency->exchange_rate);
                                }
                            }),
                            TextInput::make('exchange_rate')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            TextInput::make('debtor_foreign')->live(true)->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $set('debtor', number_format((float)str_replace(',', '', $state) * (float)str_replace(',', '', $get('exchange_rate'))));
                            })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                                        $fail('Only one of these values can be zero.');
                                    } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                        $fail('At least one of the values must be zero.');
                                    }
                                },
                            ]),
                            TextInput::make('creditor_foreign')->live(true)->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $set('creditor', number_format((float)str_replace(',', '', $state) * (float)str_replace(',', '', $get('exchange_rate'))));
                            })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                                        $fail('Only one of these values can be zero.');
                                    } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                        $fail('At least one of the values must be zero.');
                                    }
                                },
                            ]),
                        ])->columns(4)->visible(function (Get $get) {
                            return $get('isCurrency');
                        }),

                        Hidden::make('need_cheque')->default(0)->dehydrated(false)->label('Cheque/Instalment')->live(),
                        ToggleButtons::make('Cheque')->visible(fn(Get $get)=> $get('need_cheque'))->grouped()->boolean()->label('Cheque/Instalment')->live(),
                        Section::make([
                            Fieldset::make('cheque')->label('Cheque/Instalment')->relationship('cheque')->schema([
                                TextInput::make('cheque_number')->maxLength(255),
                                TextInput::make('amount')->readOnly()->default(function (Get $get) {
                                    if ($get('debtor') > 0) {
                                        return $get('debtor');
                                    } else if ($get('creditor') > 0) {
                                        return $get('creditor');
                                    } else {
                                        return 0;
                                    }
                                })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                                DatePicker::make('issue_date')->default(now())->required(),
                                DatePicker::make('due_date')->required(),
                                TextInput::make('payer_name')->label('Payor Name')->maxLength(255),
                                TextInput::make('payee_name')->maxLength(255),
                                TextInput::make('bank_name')->maxLength(255),
                                TextInput::make('branch_name')->maxLength(255),
                                Textarea::make('description')->columnSpanFull(),
                                ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                                Hidden::make('company_id')->default(getCompany()->id)
                            ])->columns(4),
                        ])->collapsible()->persistCollapsed()->visible(fn(Get $get) => $get('Cheque')),
                        Hidden::make('financial_period_id')->required()->label('Financial Period')->default(getPeriod()?->id)
                    ])->minItems(2)->columns(4)->defaultItems(2)
                        ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                            $data['user_id'] = auth()->id();
                            $data['company_id'] = getCompany()->id;
                            $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                            return $data;
                        })
                ])->columns(1)->columnSpanFull()


            ])->columnSpanFull(),


        ]);
    }
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');
            $debtor = 0;
            $creditor = 0;

            foreach ($this->data['invoice']['transactions'] as &$transaction) {

                if ($transaction['creditor'] > 0) {
                    $creditor += str_replace(',', '', $transaction['creditor']);
                } else {
                    $debtor += str_replace(',', '', $transaction['debtor']);
                }
                $transaction['user_id'] = auth()->id();
                $transaction['creditor'] = str_replace(',', '', $transaction['creditor']);
                $transaction['debtor'] = str_replace(',', '', $transaction['debtor']);
                $transaction['exchange_rate'] = str_replace(',', '', $transaction['exchange_rate']);
                $transaction['creditor_foreign'] = str_replace(',', '', $transaction['creditor_foreign']);
                $transaction['debtor_foreign'] = str_replace(',', '', $transaction['debtor_foreign']);
                if (isset($transaction['cheque']['amount'])) {
                    $transaction['cheque']['amount'] = str_replace(',', '', $transaction['cheque']['amount']);
                }
            }
//            dd($this->data['invoice']['transactions']);
            $transactions = $this->data['invoice']['transactions'];


            if ($debtor !== $creditor) {
                Notification::make('warning')->title('Creditor and Debtor not equal')->warning()->send();
                return;
            }

            $data = $this->form->getState(afterValidate: function () {
                $this->callHook('afterValidate');

                $this->callHook('beforeSave');
            });

            foreach ($transactions as $transactionData) {
                unset($transaction);
                $transaction = $this->record->invoice->transactions()->create($transactionData);
                if (isset($transactionData['cheque'])) {
                    $transaction->cheque()->create($transactionData['cheque']);
                }
            }

            $data['finance_id'] = getEmployee()->id;
            $data = $this->mutateFormDataBeforeSave($data);
            $this->handleRecordUpdate($this->getRecord(), $data);

            $this->callHook('afterSave');

            if ($this->record->purchase_request_id) {
                $this->record->purchaseRequest()->update([
                    'status' => "Finished"
                ]);
            }

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }
    protected function getRedirectUrl(): string
    {
        return PurchaseOrderResource::getUrl('index'); // TODO: Change the autogenerated stub
    }
}
