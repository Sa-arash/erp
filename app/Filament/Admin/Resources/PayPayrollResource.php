<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PayrollStatus;
use App\Filament\Admin\Resources\PayPayrollResource\Pages;
use App\Filament\Admin\Resources\PayPayrollResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Account;
use App\Models\Benefit;
use App\Models\BenefitPayroll;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Transaction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class PayPayrollResource extends Resource
    implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'payment',
        ];
    }
    protected static ?string $label="Pay Payroll";
    protected static ?string $model = Payroll::class;
    protected static ?int $navigationSort=4;
    protected static ?string $navigationIcon = 'payment';
    protected static ?string $navigationGroup = 'Finance Management';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canAccess(): bool
    {
        return \auth()->user()->can("view_any_pay::payroll");
    }
    public static function canViewAny(): bool
    {
        return \auth()->user()->can("view_any_pay::payroll");
    }

    /**
     * @return string|null
     */
    public static function getNavigationBadgeTooltip(): ?string
    {
        return "Payroll Ready to Pay";
    }
    public static function getNavigationBadge(): ?string
    {
        return self::$model::query()->where('status', 'accepted')->where('company_id', getCompany()->id)->count();
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')->query(Payroll::query()->where('company_id',getCompany()->id))
            ->groups([
                Tables\Grouping\Group::make('employee.department.title')->label('Department')->collapsible(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . "Payroll");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Payroll")->withColumns([
                            Column::make('employee_id')->formatStateUsing(fn($record) => $record->employee->fullName)->heading('Employee'),
                            Column::make('created_at')->heading("Month")->formatStateUsing(fn($record) => Carbon::parse($record->start_date)->format('F')),
                            Column::make('updated_at')->heading("Year")->formatStateUsing(fn($record) => Carbon::parse($record->start_date)->year),

                            Column::make('id')->formatStateUsing(fn($record) => number_format($record->employee->base_salary) . "" . $record->employee->currency?->symbol)->heading('Base Salary'),
                            Column::make('total_allowance')->formatStateUsing(fn($record) => number_format($record->total_allowance) . "" . $record->employee->currency?->symbol)->heading('Total Allowance'),
                            Column::make('total_deduction')->formatStateUsing(fn($record) => number_format($record->total_deduction) . "" . $record->employee->currency?->symbol)->heading('Total Deduction'),
                            Column::make('amount_pay')->formatStateUsing(fn($record) => number_format($record->amount_pay) . "" . $record->employee->currency?->symbol)->heading('Net Pay'),
                            Column::make('status'),
                        ]),
                    ])->label('Export Payroll')->color('purple'),

                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->url(function ($livewire) {
                        $query = $livewire->getTableQueryForExport()->get(); // ✔️ این متد وجود داره

                        $ids = $query->pluck('id')->toArray();

                        if (!empty($ids)) {
                            return route('pdf.payrolls', [
                                'ids' => implode('-', $ids),
                            ]);
                        }
                    },true)


            ])
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.ID_number')->label('ID Number')->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('employee.department.title')->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('month')->state(fn($record) => Carbon::parse($record->start_date)->format('F'))->alignLeft()->sortable(true,fn($query, $direction)=> $query->orderBy('start_date',$direction)),
                Tables\Columns\TextColumn::make('year')->state(fn($record) => Carbon::parse($record->start_date)->year)->alignLeft()->sortable(true,fn($query, $direction)=> $query->orderBy('start_date',$direction)),
                //   Tables\Columns\TextColumn::make('payment_date')->alignCenter()->state(fn($record) => $record->payment_date ? Carbon::make($record->payment_date)->format('Y/m/d') : "Not Paid")->sortable(),
                Tables\Columns\TextColumn::make('employee.base_salary')->state(fn($record) => number_format($record->employee->base_salary) . "" . $record->employee->currency?->symbol)->copyable()->label('Base Salary')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_allowance')->state(fn($record) => number_format($record->total_allowance) . "" . $record->employee->currency?->symbol)->copyable()->label('Total Allowance')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_deduction')->state(fn($record) => number_format($record->total_deduction) . "" . $record->employee->currency?->symbol)->copyable()->label('Total Deduction')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('amount_pay')->state(fn($record) => number_format($record->amount_pay) . "" . $record->employee->currency?->symbol)->copyable()->label('Total Net Pay')->label('Net Pay')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->alignLeft(),
            ])
            ->filters([
                SelectFilter::make('department_id')->searchable()->preload()->options(Department::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))->label('Department')->query(fn($query,$data)=> isset($data['value'])? $query->whereHas('employee',function ($query)use($data){
                    $query->where('department_id',$data);
                }):$query),
                SelectFilter::make('employee_id')->multiple()->searchable()->preload()->options(Employee::where('company_id', getCompany()->id)->get()->pluck('fullName', 'id'))->label('Employee'),
                Filter::make('filter')->form([
                    Forms\Components\Select::make('month')
                        ->searchable()
                        ->preload()
                        ->options([
                            1 => 'January',
                            2 => 'February',
                            3 => 'March',
                            4 => 'April',
                            5 => 'May',
                            6 => 'June',
                            7 => 'July',
                            8 => 'August',
                            9 => 'September',
                            10 => 'October',
                            11 => 'November',
                            12 => 'December'
                        ])
                        ->label('Month'),

                    Forms\Components\Select::make('year')
                        ->searchable()
                        ->preload()
                        ->options([
                            2025 => 2025,
                            2026 => 2026,
                            2027 => 2027,
                            2028 => 2028,
                            2029 => 2029,
                            2030 => 2030,
                            2031 => 2031
                        ])
                        ->label('Year')
                ])->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['month'],
                            fn(Builder $query, $month): Builder => $query->whereMonth('start_date', (int)$month)
                        )
                        ->when(
                            $data['year'],
                            fn(Builder $query, $year): Builder => $query->whereYear('start_date', (int)$year)
                        );
                })->columns(2),
                DateRangeFilter::make('start_date'),
                DateRangeFilter::make('end_date'),
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('payment')->slideOver()->visible(fn($record) => $record->status->value === "accepted" and auth()->user()->can('payment_pay::payroll'))->label('Payment')->tooltip('Payment')->icon('heroicon-o-credit-card')->iconSize(IconSize::Medium)->color('warning')->action(function ($data, $record) {
                    $debtor = 0;
                    $creditor = 0;
                    $debtorID = 0;

                    foreach ($data['transactions'] as $key => $transaction) {
                        if ($transaction['creditor'] > 0) {
                            $creditor += str_replace(',', '', $transaction['creditor']);
                            $debtorID = $transaction['account_id'];
                        } else {
                            $debtor += str_replace(',', '', $transaction['debtor']);
                        }
                        if ($transaction['isCurrency'] === 0) {
                            if ($transaction['creditor_foreign'] > 0 or $transaction['debtor_foreign'] > 0) {
                                Notification::make('warning')->title('Foreign Creditor Or Foreign Debtor Is Not Zero')->warning()->send();
                                return;
                            }
                            $data['transactions'][$key]['exchange_rate'] = 1;
                        }
                    }
                    if ($debtor !== $creditor) {
                        Notification::make('warning')->title('Creditor and Debtor not equal')->warning()->send();
                        return;
                    }
                    $period = FinancialPeriod::query()->firstWhere('status', "During");
                    if (!$period) {
                        Notification::make('warning')->title('Financial Period Not Find')->warning()->send();
                        return;
                    }


                    $invoice = Invoice::query()->create([
                        'name' => $data['name'],
                        'number' => $data['number'],
                        'date' => $data['date'],
                        'description' => $data['description'],
                        'reference' => $data['reference'],
                        'status' => 'final',
                        'company_id' => getCompany()->id
                    ]);
                    foreach ($data['transactions'] as $transaction) {
                        $transaction['financial_period_id'] = $period->id;
                        $transaction['invoice_id'] = $invoice->id;
                        $transaction['company_id'] = getCompany()->id;
                        $transaction['user_id'] = auth()->id();
                        if ($transaction['debtor'] === null) {
                            $transaction['debtor'] = 0;
                        } elseif ($transaction['creditor'] === null) {
                            $transaction['creditor'] = 0;
                        }
                        Transaction::query()->create($transaction);
                    }
                    $record->update([
                        'payment_date' => $data['date'],
                        'status' => 'payed',
                        'account_id' => $debtorID,
                        'reference' => $data['reference'],
                        'invoice_id' => $invoice->id

                    ]);
                    return Notification::make('Create Invoice Payroll')->success()->title('Pay Payroll')->send();
                })->form(function ($record) {
                    return [
                        Forms\Components\Section::make([
                            Forms\Components\Fieldset::make('invoice')->model(Invoice::class)->schema([
                                Forms\Components\TextInput::make('name')->default($record->employee->fullName . " " . Carbon::make($record->start_date)->format('Y/m/d') . " - " . Carbon::make($record->end_date)->format('Y/m/d') . " Payroll")->required()->maxLength(255),
                                Forms\Components\TextInput::make('number')->numeric()->required()->maxLength(255)->readOnly()->default(getCompany()->financialPeriods->where('status', "During")->first()?->invoices()->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()?->invoices()?->get()->last()->number + 1 : 1),
                                Forms\Components\DateTimePicker::make('date')->required()->default(now()),
                                Forms\Components\TextInput::make('reference')->maxLength(255),
                                MediaManagerInput::make('document')->orderable(false)->folderTitleFieldName("title")
                                    ->disk('public')
                                    ->schema([])->maxItems(1)->defaultItems(0)->columnSpanFull(),
                                Forms\Components\Textarea::make('description')->nullable()->columnSpanFull(),
                            ]),
                            Forms\Components\Section::make([
                                Forms\Components\Repeater::make('transactions')->label('')->schema([
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
                                        $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                                        if ($account) {
                                            $set('currency_id', $account->currency_id);
                                            $set('exchange_rate', number_format($account->currency->exchange_rate));
                                            return $set('isCurrency', 1);
                                        }
                                        return $set('isCurrency', 0);
                                    })->live()->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id))->searchable(),
                                    Forms\Components\TextInput::make('description')->required(),

                                    Forms\Components\TextInput::make('debtor')->label('Debit')->prefix(defaultCurrency()->symbol)->live(true)->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {})->mask(RawJs::make('$money($input)'))->readOnly(function (Get $get) {
                                        return $get('isCurrency');
                                    })->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                        fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ($get('debtor') == 0 && $get('creditor') == 0) {
                                                $fail('Only one of these values can be zero.');
                                            } elseif ($get('debtor') != 0 && $get('creditor') != 0) {
                                                $fail('At least one of the values must be zero.');
                                            }
                                        },
                                    ]),
                                    Forms\Components\TextInput::make('creditor')->label('Credit')->prefix(defaultCurrency()->symbol)->readOnly(function (Get $get) {
                                        return $get('isCurrency');
                                    })->live(true)

                                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                        ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                                        ->rules([
                                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
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
                                        Select::make('currency_id')->model(Transaction::class)->live()->label('Currency')->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
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
                                            $set('debtor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',', '', $get('exchange_rate'))));
                                        })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                if ($get('debtor_foreign') == 0 && $get('creditor_foreign') == 0) {
                                                    $fail('Only one of these values can be zero.');
                                                } elseif ($get('debtor_foreign') != 0 && $get('creditor_foreign') != 0) {
                                                    $fail('At least one of the values must be zero.');
                                                }
                                            },
                                        ]),
                                        Forms\Components\TextInput::make('creditor_foreign')->live(true)->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                            $set('creditor', number_format((float) str_replace(',', '', $state) * (float) str_replace(',', '', $get('exchange_rate'))));
                                        })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)->rules([
                                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
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
                                ])->minItems(2)->columns(5)->defaultItems(2)
                                    ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                                        $data['user_id'] = auth()->id();
                                        $data['company_id'] = getCompany()->id;
                                        $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                                        return $data;
                                    })
                            ])->columns(1)->columnSpanFull()
                        ])->columns(2)
                    ];
                })->modalSubmitActionLabel('Payment')->modalWidth(MaxWidth::Full),

                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->action(fn($record,$data) => redirect(route('pdf.payroll', ['id' => $record->id,'title'=>$data['title']])))->form([
                        Select::make('title')->searchable()->default('Payroll')->options(['Payroll'=>'Payroll','PaySlip'=>'PaySlip'])->required()
                    ]),
                Tables\Actions\EditAction::make(),
                ActionsDeleteAction::make()->hidden(fn($record)=>$record->invoice!==null || $record->status === "accepted"),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . "Payroll");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Payroll")->withColumns([
                            Column::make('employee_id')->formatStateUsing(fn($record) => $record->employee->fullName)->heading('Employee'),
                            Column::make('created_at')->heading("Month")->formatStateUsing(fn($record) => Carbon::parse($record->start_date)->format('F')),
                            Column::make('updated_at')->heading("Year")->formatStateUsing(fn($record) => Carbon::parse($record->start_date)->year),

                            Column::make('id')->formatStateUsing(fn($record) => number_format($record->employee->base_salary) . "" . $record->employee->currency?->symbol)->heading('Base Salary'),
                            Column::make('total_allowance')->formatStateUsing(fn($record) => number_format($record->total_allowance) . "" . $record->employee->currency?->symbol)->heading('Total Allowance'),
                            Column::make('total_deduction')->formatStateUsing(fn($record) => number_format($record->total_deduction) . "" . $record->employee->currency?->symbol)->heading('Total Deduction'),
                            Column::make('amount_pay')->formatStateUsing(fn($record) => number_format($record->amount_pay) . "" . $record->employee->currency?->symbol)->heading('Net Pay'),
                            Column::make('status'),
                        ]),
                    ])->label('Export Payroll')->color('purple'),

                Tables\Actions\BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPayPayrolls::route('/'),
            'create' => Pages\CreatePayPayroll::route('/create'),
            'edit' => Pages\EditPayPayroll::route('/{record}/edit'),
        ];
    }
}
