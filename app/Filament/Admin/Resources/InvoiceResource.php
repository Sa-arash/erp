<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Filament\Admin\Resources\InvoiceResource\RelationManagers;
use App\Filament\Exports\InvoiceExporter;
use App\Models\Account;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\ExportAction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $label = 'Voucher';
    protected static ?string $pluralLabel = 'Journal Entry';
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationLabel =  'Journal Entry';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationGroup = 'Finance Management';


    // public static function canAccess(): bool
    // {
    //     return getPeriod()?->id !==null; // TODO: Change the autogenerated stub
    // }

    public static function canEdit(Model $record): bool
    {
        if (isset($record->transactions[0])) {
            return ($record->transactions[0]->financialPeriod->end_date > now());
        }
        return false;
    }


    // public static function canAccess(): bool
    // {
    //     $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
    //     if (!$period) {
    //         return false;
    //     }
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        SelectTree::make('account_id')->formatStateUsing(function ($state, Forms\Set $set) {
                            $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                            if ($account) {
                                $set('currency_id', $account->currency_id);
                                $set('exchange_rate', number_format($account->currency->exchange_rate));
                                $set('isCurrency', 1);
                                return $state;
                            }
                            $set('isCurrency', 0);
                            return $state;
                        })->afterStateUpdated(function ($state, Forms\Set $set) {
                            $query = Account::query()->find($state);
                            // dd($query);
                            if($query->has_cheque == 1){
                                $set('Cheque',true);
                            }else{
                                $set('Cheque',false);
                            }
                            $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                            // dd($account);
                            if ($account) {
                                $set('currency_id', $account->currency_id);
                                $set('exchange_rate', number_format($account->currency->exchange_rate));
                                return $set('isCurrency', 1);
                            }
                            return $set('isCurrency', 0);
                        })->live()->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id))->searchable(),
                        Forms\Components\TextInput::make('description')->required(),

                        Forms\Components\TextInput::make('debtor')->prefix(defaultCurrency()->symbol)->live(true)->afterStateUpdated(function ($state, Forms\Set $set,Get $get) {
                            if ($get('Cheque')) {
                                $set('cheque.amount', $state);
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
                        Forms\Components\TextInput::make('creditor')->prefix(defaultCurrency()->symbol)->readOnly(function (Get $get) {
                            return $get('isCurrency');
                        })->live(true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                if ($get('Cheque')) {
                                    $set('cheque.amount', $state);
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
                        Forms\Components\Hidden::make('isCurrency'),
                        Forms\Components\Hidden::make('currency_id')->default(defaultCurrency()?->id)->hidden(function (Get $get) {
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
                            ])->afterStateUpdated(function ($state, Forms\Set $set) {
                                $currency = Currency::query()->firstWhere('id', $state);
                                if ($currency) {
                                    $set('exchange_rate', $currency->exchange_rate);
                                }
                            })->editOptionAction(function ($state, Forms\Set $set) {
                                $currency = Currency::query()->firstWhere('id', $state);
                                if ($currency) {
                                    $set('exchange_rate', $currency->exchange_rate);
                                }
                            }),
                            TextInput::make('exchange_rate')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Forms\Components\TextInput::make('debtor_foreign')->live(true)->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                $set('debtor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',','',$get('exchange_rate'))));
                            })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                                        $fail('Only one of these values can be zero.');
                                    } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                        $fail('At least one of the values must be zero.');
                                    }
                                },
                            ]),
                            Forms\Components\TextInput::make('creditor_foreign')->live(true)->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                $set('creditor',number_format((float) str_replace(',', '', $state) * (float) str_replace(',','',$get('exchange_rate'))));
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
                        Forms\Components\Checkbox::make('Cheque')->label('Cheque/Instalment')->inline()->live(),
                        Forms\Components\Section::make([
                            Forms\Components\Fieldset::make('cheque')->label('Cheque/Instalment')->relationship('cheque')->schema([
                                Forms\Components\TextInput::make('cheque_number')->maxLength(255),
                                Forms\Components\TextInput::make('amount')->readOnly()->default(function (Get $get) {
                                    if ($get('debtor') > 0) {
                                        return $get('debtor');
                                    }
                                    else if ($get('creditor') > 0) {
                                        return $get('creditor');
                                    }else {
                                        return 0;
                                    }
                                })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                                Forms\Components\DatePicker::make('issue_date')->default(now())->required(),
                                Forms\Components\DatePicker::make('due_date')->required(),
                                Forms\Components\TextInput::make('payer_name')->maxLength(255),
                                Forms\Components\TextInput::make('payee_name')->maxLength(255),
                                Forms\Components\TextInput::make('bank_name')->maxLength(255),
                                Forms\Components\TextInput::make('branch_name')->maxLength(255),
                                Forms\Components\Textarea::make('description')->columnSpanFull(),
                                Forms\Components\ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                                Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                            ]),
                        ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                        Forms\Components\Hidden::make('financial_period_id')->required()->label('Financial Period')->default(getPeriod()?->id)
                    ])->minItems(2)->columns(5)->defaultItems(2)
                        ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                            $data['user_id'] = auth()->id();
                            $data['company_id'] = getCompany()->id;
                            $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                            return $data;
                        })
                ])->columns(1)->columnSpanFull()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(InvoiceExporter::class)->color('purple')
            ])
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('number')->searchable()->label('Voucher NO'),
                Tables\Columns\TextColumn::make('date')->state(fn($record) => Carbon::parse($record->date)->format("Y-m-d")),
                Tables\Columns\TextColumn::make('name')->searchable()->label('Voucher Title'),
                Tables\Columns\TextColumn::make('reference')->searchable(),
            ])
            ->filters([
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('name')->label('Voucher Title'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('number')->label('Voucher NO'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('reference')->label('Reference'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('description')->label('Description'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('record_description')->relationship(name: 'transactions', titleAttribute: 'description'),
                        Tables\Filters\QueryBuilder\Constraints\DateConstraint::make('date'),

                    ]),

            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('')->iconSize(IconSize::Large)
                    ->icon('heroicon-o-printer')
                    ->url(fn($record) => route('pdf.document', ['document' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportAction::make()
                        ->exporter(InvoiceExporter::class)->color('purple')
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
