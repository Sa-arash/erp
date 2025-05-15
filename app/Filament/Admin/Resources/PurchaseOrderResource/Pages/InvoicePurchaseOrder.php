<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Parties;
use App\Models\Product;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;

class InvoicePurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function mount(int | string $record): void
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
            Group::make()->relationship('invoice')->schema([

                Hidden::make('company_id')->default(getCompany()->id)->required(),
                Section::make([
                    TextInput::make('number')
                        ->columnSpan(1)
                        ->default(getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1)->label('Voucher Number')->required()->maxLength(255)->readOnly(),
                    TextInput::make('name')
                        ->columnSpan(3)
                        ->label('Voucher Title')->required()->maxLength(255),
                    TextInput::make('reference')
                        ->columnSpan(1)
                        ->maxLength(255),
                    DatePicker::make('date')
                        ->columnSpan(2)
                        ->required()->default(now()),
                    FileUpload::make('document')->placeholder('Browse')->extraInputAttributes(['style' => 'height:30px!important;'])
                        ->nullable(),
                    Placeholder::make('total :')->live()->content(function (Get $get) {

                        if ($this->record->items->toArray()) {
                            $produtTotal = array_map(function ($item) {
                                // dd($item);
                                try {
                                    //code...
                                    return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100));
                                } catch (\Throwable $th) {
                                    //throw $th;
                                    return null;
                                }
                            }, $this->record->items->toArray());

                            return  collect($produtTotal)->sum() ? number_format(collect($produtTotal)->sum()) : '?';
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
                        })->afterStateUpdated(function ($state, Set $set) {
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
                                } else {
                                    $set('Cheque', false);
                                }
                            } else {
                                $set('Cheque', false);
                            }

                            $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                            if ($account) {
                                $set('currency_id', $account->currency_id);
                                $set('exchange_rate', number_format($account->currency->exchange_rate));
                                return $set('isCurrency', 1);
                            }
                            return $set('isCurrency', 0);
                        })->live()->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('group', 'asset')->where('company_id', getCompany()->id))->searchable(),
                        TextInput::make('description')->required(),

                        TextInput::make('debtor')->prefix(defaultCurrency()->symbol)->mask(RawJs::make('$money($input)'))->readOnly()->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                            ->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail, $operation) use ($get) {
                                    if ($operation == "create") {



                                        if ($get('debtor') != 0) {
                                            $fail('The debtor field must be zero.');
                                        }
                                    } else {
                                        if ($get('debtor') == 0 && $get('creditor') == 0) {
                                            $fail('Only one of these values can be zero.');
                                        } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    }
                                },
                            ]),
                        TextInput::make('creditor')->prefix(defaultCurrency()->symbol)
                            ->readOnly(function (Get $get) {
                                return $get('isCurrency');
                            })
                            ->live(true)->afterStateUpdated(function ($state, Set $set, Get $get) {

                                $set('cheque.amount', $state);
                            })
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                            ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                            ->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail, $operation) use ($get) {


                                    if ($get('creditor') == 0) {
                                        $fail('The creditor field must be not zero.');
                                    } else {

                                        // dd($get->getData()['invoice']['transactions']);
                                        $produtTotal = array_map(function ($item) {
                                            // dd($item);
                                            return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100));
                                        }, $this->record->items->toArray());

                                        $invoiceTotal = array_map(function ($item) {
                                            // dd($item);
                                            return (str_replace(',', '', $item['creditor']));
                                        }, $get->getData()['invoice']['transactions']);
                                        $productSum = collect($produtTotal)->sum();
                                        $invoiceSum = collect($invoiceTotal)->sum();
                                        // dd($productSum,$invoiceSum,($invoiceSum != $productSum));

                                        if ($invoiceSum != $productSum) {
                                            $remainingAmount = $productSum - $invoiceSum;
                                            $fail("The paid amount does not match the total price. Total amount:" . number_format($productSum) . ", Remaining amount: " . number_format($remainingAmount));
                                        }

                                        if ($get('debtor') == 0 && $get('creditor') == 0) {
                                            $fail('Only one of these values can be zero.');
                                        } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    }
                                },
                            ]),
                        Hidden::make('isCurrency'),
                        Hidden::make('currency_id')->default(defaultCurrency()?->id)->hidden(function (Get $get) {
                            return $get('isCurrency');
                        }),
                        Section::make([
                            Select::make('currency_id')->live()->label('Currency')->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
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
                            TextInput::make('exchange_rate')->default(defaultCurrency()->exchange_rate)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            TextInput::make('debtor_foreign')
                                ->readOnly()
                                ->live(true)->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $set('debtor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',', '', $get('exchange_rate'))));
                                })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                        if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                                            $fail('Only one of these values can be zero.');
                                        } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                            $fail('At least one of the values must be zero.');
                                        }
                                    },
                                ]),
                            TextInput::make('creditor_foreign')
                                ->live(true)->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $set('creditor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',', '', $get('exchange_rate'))));
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
                        Hidden::make('Cheque')->label('Cheque/Instalment')->live(),
                        Section::make([
                            Fieldset::make('cheque')->label('Cheque/Instalment')->relationship('cheque')->schema([
                                TextInput::make('cheque_number')->maxLength(255),
                                TextInput::make('amount')->default(function (Get $get) {
                                    if ($get('debtor') > 0) {
                                        return $get('debtor');
                                    } else
                                                    if ($get('creditor') > 0) {
                                        return $get('creditor');
                                    } else {
                                        return 0;
                                    }
                                })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                                DatePicker::make('issue_date')->required()->default(now()),
                                DatePicker::make('due_date')->required(),
                                TextInput::make('payer_name')->maxLength(255),
                                TextInput::make('payee_name')->maxLength(255),
                                TextInput::make('bank_name')->maxLength(255),
                                TextInput::make('branch_name')->maxLength(255),
                                Textarea::make('description')->columnSpanFull(),
                                ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                                Hidden::make('company_id')->default(getCompany()->id)
                            ]),
                        ])->collapsible()->persistCollapsed()->visible(fn(Get $get) => $get('Cheque')),
                        Hidden::make('financial_period_id')->required()->label('Financial Period')->default(getPeriod()?->id)
                    ])->minItems(1)->columns(4)->defaultItems(1)
                        ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                            $data['user_id'] = auth()->id();
                            $data['company_id'] = getCompany()->id;
                            $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                            return $data;
                        })
                ])->columnSpanFull()


            ])->columnSpanFull(),


        ]);
    }
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState(afterValidate: function () {
                $this->callHook('afterValidate');

                $this->callHook('beforeSave');
            });
            
            $data = $this->mutateFormDataBeforeSave($data);
            
            $this->handleRecordUpdate($this->getRecord(), $data);
            
            $this->callHook('afterSave');
            // dd($this->data,$this->record->invoice);


  ########################################################################################################
            
            $total = 0;
            foreach ($this->record->items->toArray() as $item) {
                $total += str_replace(',', '', $item['total']);
            }

// dd($this->data['invoice']['transactions']);
            DB::beginTransaction(); // شروع تراکنش

            try {

                //     // ذخیره فاکتور (Invoice)
                
                $this->record->invoice->update([
                    'name' => $this->record->invoice->name . "(Total:" . number_format($total) . ")",
                ]);
                    // ذخیره تراکنش‌های فاکتور (Transactions)
                // foreach ($this->data['invoice']['transactions'] as $transaction) {
                //     $savedTransaction = $this->record->invoice->transactions()->create([
                //         'account_id' => $transaction['account_id'],
                //         'description' => $transaction['description'] . " PONO:" . $this->record->purchase_orders_number . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : ""),
                //         'company_id' => $transaction['company_id'],
                //         'user_id' => auth()->user()->id,
                //         'creditor' => str_replace(',', '', $transaction['creditor']),
                //         'debtor' => 0,
                //         "currency_id" => $transaction['currency_id'] ?? defaultCurrency()->id,
                //         "exchange_rate" => str_replace(',', '', $transaction['exchange_rate']) ?? defaultCurrency()->exchange_rate,
                //         "debtor_foreign" => 0,
                //         "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign']),
                //         'Cheque' => $transaction['Cheque'],
                //         'financial_period_id' => $transaction['financial_period_id'],
                //     ]);
                //     // dd($transaction ,!empty($transaction['cheque']) && isset($transaction['cheque']['amount']) );
                //     // چک
                //     if ($transaction['Cheque']) {
                //         $savedTransaction->cheque()->create([
                //             'type' => $transaction['cheque']['type'] ?? null,
                //             'bank_name' => $transaction['cheque']['bank_name'] ?? null,
                //             'branch_name' => $transaction['cheque']['branch_name'] ?? null,
                //             'account_number' => $transaction['cheque']['account_number'] ?? null,
                //             'amount' => str_replace(',', '', $transaction['cheque']['amount']),
                //             'issue_date' => $transaction['cheque']['issue_date'] ?? null,
                //             'due_date' => $transaction['cheque']['due_date'] ?? null,
                //             'status' => $transaction['cheque']['status'] ?? null,
                //             'payer_name' => $transaction['cheque']['payer_name'] ?? null,
                //             'payee_name' => $transaction['cheque']['payee_name'] ?? null,
                //             'description' => $transaction['cheque']['description'] ?? null,
                //             'company_id' => $transaction['cheque']['company_id'] ?? null,
                //             'status' => 'pending',
                //             'cheque_number' => $transaction['cheque']['cheque_number'] ?? null,
                //             'transaction_id' => $savedTransaction->id, // اتصال چک به تراکنش
                //         ]);
                //     }
                // }

                DB::commit(); // تایید تراکنش

                //     return response()->json(['message' => 'Purchase Order Created Successfully', 'data' => $purchaseOrder], 201);

            } catch (\Exception $e) {
                dd($e);
                DB::rollBack(); // لغو تراکنش در صورت خطا
                // return response()->json(['message' => 'Error occurred', 'error' => $e->getMessage()], 500);
            }




            #fix
            $vendorAccount = Parties::find($this->record->vendor_id);
            // dd($vendorAccount,$vendorAccount->accountVendor->id);


            //Giving money to Vendor
            $savedTransaction = $this->record->invoice->transactions()->create([

                'account_id' => $vendorAccount->accountVendor->id,
                'user_id' => auth()->user()->id,
                'creditor' => 0,
                "currency_id" => $vendorAccount->accountVendor->currency_id,
                "exchange_rate" => $vendorAccount->accountVendor->currency->exchange_rate,
                "debtor_foreign" => $total != 0 ? $total / $vendorAccount->accountVendor->currency->exchange_rate : 0,
                'debtor' => $total,
                'description' => 'Giving money to ' . $vendorAccount->name . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : "" . " PONO:" . $this->record->purchase_orders_number),
                'company_id' => getCompany()->id,
                'financial_period_id' => getPeriod()->id,
                //  'invoice_id' => $invoice->id,


            ]);

            // Giving assets by vendor
            $savedTransaction = $this->record->invoice->transactions()->create([

                'account_id' => $vendorAccount->accountVendor->id,
                'user_id' => auth()->user()->id,

                "currency_id" => $vendorAccount->accountVendor->currency_id,
                "exchange_rate" => $vendorAccount->accountVendor->currency->exchange_rate,
                "creditor_foreign" => $total != 0 ? ($total / $vendorAccount->accountVendor->currency->exchange_rate) : 0,

                'debtor' => 0,
                'creditor' => $total,
                'description' => 'Get assets from ' . $vendorAccount->name . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : "" . " PONO:" . $this->record->purchase_orders_number),
                'company_id' => getCompany()->id,
                'financial_period_id' => getPeriod()->id,
                //  'invoice_id' => $invoice->id,


            ]);

            //Added each product to asset

            foreach ($this->record->items->toArray() as $item) {

                $product = Product::find($item['product_id']);
                $savedTransaction = $this->record->invoice->transactions()->create([



                    'account_id' => $product->sub_account_id,
                    'user_id' => auth()->user()->id,
                    'creditor' => 0,
                    'debtor' => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)),
                    'description' => 'Added ' . $product->title . ' to assets ' . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : "" . " PONO:" . $this->record->purchase_orders_number),
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,

                    "currency_id" => $product->subAccount->currency_id,
                "exchange_rate" => $product->subAccount->currency->exchange_rate,
                "debtor_foreign" => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)) != 0 ? (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)) / $product->subAccount->currency->exchange_rate : 0,


                ]);

                // $invoice->transactions()->create([
                //     'account_id' => $item['product_id'],
                //     'creditor' => 0,
                //     'debtor' => str_replace(',', '', $item['total']),
                //     'description' => 'item buy from ' . $item['purchase_request_id'] ?? '',

                //     'invoice_id' => $invoice->id,
                //     'financial_period_id' => getPeriod()->id,
                //     'company_id' => getCompany()->id,
                //     'user_id' => auth()->user(),
                // ]);
            }

            if ($this->record->purchase_request_id) {
                $this->record->purchaseRequest()->update([
                    'status' => "Finished"
                ]);
            }
            
            ##############################333
























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
