<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Account;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
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
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\RawJs;

class CreateInvoicePurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;
   
    // public function mount(): void
    // {
    //     $this->fillForm();
    //     // $this->record = PurchaseOrder::find($this->id);

    //     $this->previousUrl = url()->previous();

    // }
    // public static function canAccess(array $parameters = []): bool
    // {
    //     // $url=request('tk');
    //     // if ($url){
    //     //     if ($url==="my"){
    //     //         $PR=PurchaseRequest::query()->where('employee_id',getEmployee()->id)->where('id',request('id'))->first();
    //     //         if ($PR)
    //     //             return true ;
    //     //     }elseif ($url==="resource"){
    //     //         return true;
    //     //     }
    //     // }
    //     // return false;
    // }
    public function mountCanAuthorizeResourceAccess(): void
    {
    }

    public static function authorizeResourceAccess(): void
    {
    }

    public function create(bool $another = false): void
    {

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

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

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canCreate(), 403);
    }

    public function afterFill()
    {
        // $PR = PurchaseRequest::query()->with(['items', 'items.media'])->firstWhere('id', request('id'));
        // if (!$PR) {
        //     abort(404);
        // }
        // $PR = $PR->toArray();
        // $puncher = PurchaseRequest::query()->where('company_id', getCompany()->id)->latest()->first();
        // if ($puncher) {
        //     $PR['purchase_number'] = generateNextCodePO($puncher->purchase_number);
        // }
        // $PR['request_date'] = now()->format('Y-m-d H:i:s');
        // foreach ($PR['items'] as $key => $item) {
        //     $product = Product::query()->firstWhere('id', $item['product_id']);
        //     $PR['items'][$key]['department_id'] = $product->department_id;
        //     $PR['items'][$key]['type'] = $product->product_type=='Service' ?0 :1;
        //     $PR['items'][$key]['document'] = $item['media'];
        // }
        // $PR['status']="Requested";
        // $this->data = $PR;
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
                                        if ($get->getData()['RequestedItems']) {
                                            $produtTotal = array_map(function ($item) {
                                                // dd($item);
                                                try {
                                                    //code...
                                                    return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100));
                                                } catch (\Throwable $th) {
                                                    //throw $th;
                                                    return null;
                                                }
                                            }, $get->getData()['RequestedItems']);

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

                                                    if ($operation == "create") {
                                                        if ($get('creditor') == 0) {
                                                            $fail('The creditor field must be not zero.');
                                                        } else {

                                                            // dd(()));
                                                            $produtTotal = array_map(function ($item) {
                                                                // dd($item);
                                                                return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100));
                                                            }, $get->getData()['RequestedItems']);

                                                            $invoiceTotal = array_map(function ($item) {
                                                                // dd($item);
                                                                return (str_replace(',', '', $item['creditor']));
                                                            }, $get->getData()['invoice']['transactions']);

                                                            $productSum = collect($produtTotal)->sum();
                                                            $invoiceSum = collect($invoiceTotal)->sum();

                                                            if ($invoiceSum != $productSum) {
                                                                $remainingAmount = $productSum - $invoiceSum;
                                                                $fail("The paid amount does not match the total price. Total amount:" . number_format($productSum) . ", Remaining amount: " . number_format($remainingAmount));
                                                            }
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
    
    public function afterCreate(){
        $request=$this->record;
        sendApprove($request,'PR Warehouse (1)_approval');
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl;
    }
}
