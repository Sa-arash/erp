<?php

namespace App\Filament\Admin\Resources\FinancialPeriodResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Models\Account;
use App\Models\Cheque;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

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
                Forms\Components\Repeater::make('transactions')->reorderable(false)->label('')->schema([
                    SelectTree::make('account_id')->defaultOpenLevel(4)->label('Account')->required()->searchable()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->whereIn('group', ['Assets', 'Liabilities', "Equity"])->where('company_id', getCompany()->id)),
                    Forms\Components\TextInput::make('description')->default('Opening Journal Entry ')->required(),
                    Forms\Components\TextInput::make('debtor')
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
                    Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                    Forms\Components\Section::make([
                        Forms\Components\Fieldset::make('cheque')->model(Transaction::class)->schema([
                            Forms\Components\TextInput::make('cheque_number')->required()->maxLength(255),
                            Forms\Components\TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                            Forms\Components\DatePicker::make('issue_date')->required(),
                            Forms\Components\DatePicker::make('due_date')->required(),
                            Forms\Components\TextInput::make('payer_name')->required()->maxLength(255),
                            Forms\Components\TextInput::make('payee_name')->required()->maxLength(255),
                            Forms\Components\TextInput::make('bank_name')->maxLength(255),
                            Forms\Components\TextInput::make('branch_name')->maxLength(255),
                            Forms\Components\Textarea::make('description')->columnSpanFull(),
                            Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                        ]),
                    ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                ])->minItems(1)->columns(4)->defaultItems(1)->columnSpanFull()->formatStateUsing(function () {
                    $array = [];
                    $finance = FinancialPeriod::query()->where('status', 'Before')->where('company_id', getCompany()->id)->first();
                    $children = [];
                    $accounts = Account::query()->whereIn('stamp', ['Assets', 'Liabilities', 'Equity'])->with('childerns')->where('company_id', getCompany()->id)->get();
                    foreach ($accounts as $account) {
                        if ($account->childerns) {
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

                    if (isset($finance->transactions[0])) {
                        $arrayData = [];
                        $arr=[];
                        foreach ($finance->transactions()->with('cheque')->get()->toArray() as $value) {
                            if (isset($value['cheque']['id'])) {
                                $value['Cheque'] = true;
                                $arrayData[]=[...$value,...$value['cheque']];
                            }else{
                                $arrayData[] = $value;

                            }
                        }
                        return $arrayData;
                    } else {
                        foreach (array_unique($children) as $child) {
                            $array[] = [
                                'account_id' => $child,
                                'description' => "Opening Journal Entry " . $finance->name,
                                'debtor' => 0,
                                'creditor' => 0,
                            ];
                        }
                        return $array;
                    }
                })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('account.name')
                    ->alignCenter(),
                    Tables\Columns\TextColumn::make('account.code')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('description')->alignCenter(),
                Tables\Columns\TextColumn::make('debtor')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('creditor')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('creditor')->alignCenter()->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('start')
                    ->requiresConfirmation()
                    ->label('Start During the Financial Year')->action(function ($record) {

                        if (isset($this->record->transactions[0])) {
                            // $assetID = Account::firstWhere('stamp', 'Assets')->id;
                            // $libilityID = Account::firstWhere('stamp', 'Liabilities')->id;
                            // $equityID = Account::firstWhere('stamp', 'Equity')->id;

                            // $labilitis = Account::query()->where('id', $libilityID)
                            //     ->orWhere('parent_id', $libilityID)
                            //     ->orWhereHas('account', function ($query) use ($libilityID) {
                            //         return $query->where('parent_id', $libilityID)->orWhereHas('account', function ($query) use ($libilityID) {
                            //             return $query->where('parent_id', $libilityID);
                            //         });
                            //     })
                            //     ->pluck('id')->toArray();
                            // $assets = Account::query()->where('id', $assetID)
                            //     ->orWhere('parent_id', $assetID)
                            //     ->orWhereHas('account', function ($query) use ($assetID) {
                            //         return $query->where('parent_id', $assetID)->orWhereHas('account', function ($query) use ($assetID) {
                            //             return $query->where('parent_id', $assetID);
                            //         });
                            //     })
                            //     ->pluck('id')->toArray();

                            // $assetSum = $this->record->transactions->whereIn('id', $assets)->sum('debtor')-$this->record->transactions->whereIn('id', $assets)->sum('creditor');
                            // $libilitySum = $this->record->transactions->whereIn('id', $labilitis);
                            $debtor = $this->record->transactions->sum('debtor');
                            $creditor = $this->record->transactions->sum('creditor');
                            $equity = $debtor - $creditor;
                            // dd($debtor,$creditor,$equity);

                            if ($equity !== 0) {
                                $this->record->transactions()->create([
                                    'account_id' => Account::query()->where('stamp', 'Equity')->where('company_id', getCompany()->id)->first()->id,
                                    'creditor' => $equity >= 0 ? $equity : 0,
                                    'debtor' => $equity <= 0 ? abs($equity) : 0,
                                    'description' => 'auto generate equity',
                                    'company_id' => getCompany()->id,
                                    'user_id' => auth()->user()->id,
                                    'invoice_id' => $this->record->transactions[0]->invoice_id,
                                    'financial_period_id' => $this->record->id,
                                ]);
                            }

                        }

                        $this->record->update(['status' => 'During']);
                        $url = "admin/" . getCompany()->id . "/finance-settings/financial-periods";
                        return redirect($url);
                    }),
                Tables\Actions\CreateAction::make()->label(function () {
                    $finance = FinancialPeriod::query()->where('status', 'Before')->where('company_id', getCompany()->id)->first();
                    if (isset($finance->transactions[0])) {
                        return "Edit Record";
                    } else {
                        return "New Record";
                    }
                })->closeModalByClickingAway(false)->modalWidth(MaxWidth::Full)->action(function ($data) {
                    $finance = FinancialPeriod::query()->where('status', 'Before')->where('company_id', getCompany()->id)->first();
                    $title = "Opening Journal Entry " . $finance->name;
                    $invoice = Invoice::query()->firstWhere('name', $title);


                    if ($invoice) {
                        foreach ($invoice?->transactions as $transaction) {
                            $transaction->delete();
                        }
                        $invoice->update(['date'=>$data['date']]);

                        foreach ($data['transactions'] as $transaction) {
                            if ($transaction['debtor'] > 0 or $transaction['creditor'] > 0) {
                                $transaction['financial_period_id'] = $finance->id;
                                $transaction['invoice_id'] = $invoice->id;
                                $transaction['company_id'] = getCompany()->id;
                                $transaction['user_id'] = auth()->id();
                                $record = Transaction::query()->create($transaction);
                                if ($transaction['Cheque']) {
                                    $transaction['company_id'] = getCompany()->id;
                                    $transaction['amount'] = str_replace(',', '', $transaction['amount']);
                                    $transaction['transaction_id'] = $record->id;
                                    $type = str_replace(',', '', $transaction['debtor']) > 0 ? 0 : 1;
                                    Cheque::query()->create([
                                        'type' => $type
                                        , "bank_name" => $transaction['bank_name']
                                        , "branch_name" => $transaction['branch_name']
                                        , "amount" => $transaction['amount']
                                        , "issue_date" => $transaction['issue_date']
                                        , "due_date" => $transaction['due_date']
                                        , "payer_name" => $transaction['payer_name']
                                        , "payee_name" => $transaction['payee_name']
                                        , "description" => $transaction['description']
                                        , "company_id" => $transaction['company_id']
                                        , "cheque_number" => $transaction['cheque_number']
                                        , 'transaction_id' => $record->id
                                    ]);
                                }
                            }
                        }
                    } else {
                        $invoice = Invoice::query()->create(['name' => $title, 'number' => 1, 'date' => $data['date'], 'description' => null, 'reference' => null, 'company_id' => getCompany()->id, 'document' => null]);
                        if ($invoice) {
                            foreach ($data['transactions'] as $transaction) {
                                if ($transaction['debtor'] > 0 or $transaction['creditor'] > 0) {
                                    $transaction['financial_period_id'] = $finance->id;
                                    $transaction['invoice_id'] = $invoice->id;
                                    $transaction['company_id'] = getCompany()->id;
                                    $transaction['user_id'] = auth()->id();
                                    $record = Transaction::query()->create($transaction);
                                    if ($transaction['Cheque']) {
                                        $transaction['cheque']['company_id'] = getCompany()->id;
                                        $transaction['cheque']['amount'] = str_replace(',', '', $transaction['amount']);
                                        $transaction['cheque']['transaction_id'] = $record->id;
                                        $type = str_replace(',', '', $transaction['debtor']) > 0 ? 0 : 1;
                                        Cheque::query()->create([
                                            'type' => $type
                                            , "bank_name" => $transaction['bank_name']
                                            , "branch_name" => $transaction['branch_name']
                                            , "amount" => $transaction['amount']
                                            , "issue_date" => $transaction['issue_date']
                                            , "due_date" => $transaction['due_date']
                                            , "payer_name" => $transaction['payer_name']
                                            , "payee_name" => $transaction['payee_name']
                                            , "description" => $transaction['description']
                                            , "company_id" => $transaction['company_id']
                                            , "cheque_number" => $transaction['cheque_number']
                                            , 'transaction_id' => $record->id
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }),
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
