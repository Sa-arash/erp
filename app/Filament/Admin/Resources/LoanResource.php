<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LoanStatus;
use App\Filament\Admin\Resources\LoanResource\Pages;
use App\Filament\Admin\Resources\LoanResource\RelationManagers;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'loan';
    protected static ?string $navigationGroup = 'HR Management System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->suffixIcon('employee')
                    ->suffixIconColor('primary')
                    ->label('Employee')
                    ->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('loan_code')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('request_amount')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('amount')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->suffixIcon('cash')
                    ->suffixIconColor('success')
                    ->minValue(0)
                    ->nullable()
                    ->numeric(),
                Forms\Components\TextInput::make('number_of_installments')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('number_of_payed_installments')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\DatePicker::make('request_date')
                    ->required(),
                Forms\Components\DatePicker::make('answer_date')
                    ->label('Approval Date'),
                Forms\Components\DatePicker::make('first_installment_due_date') // فیلد تاریخ سررسید اولین قسط
                    ->label('First Installment Due Date')
                    ->required(), // اگر می‌خواهید این فیلد الزامی باشد
                Forms\Components\TextInput::make('description') // فیلد توضیحات
                    ->label('Description')
                    ->nullable() // این فیلد می‌تواند خالی باشد
                    ->maxLength(255), // حداکثر طول ورودی
                Forms\Components\ToggleButtons::make('status')
                    ->options(LoanStatus::class)
                    ->inline()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),

                Tables\Columns\TextColumn::make('employee.fullName')->alignCenter()->columnSpanFull()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loan_code')->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_amount')->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_installments')->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_payed_installments')->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_date')->alignCenter()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('answer_date')->label('Approval Date')->alignCenter()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->alignCenter(),

            ])
            ->filters([

                SelectFilter::make('employee_id')->searchable()->preload()->options(Employee::where('company_id', getCompany()->id)->get()->pluck('fullName', 'id'))
                    ->label('employee'),


                SelectFilter::make('status')->searchable()->preload()->options(LoanStatus::class),

                Filter::make('request_date')
                    ->form([
                        Forms\Components\Section::make([
                            TextInput::make('min')->label('Min Request Date')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                ->numeric(),

                            TextInput::make('max')->label('Max Request Date')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                ->numeric(),
                        ])->columns()
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('request_date', '>=', str_replace(',', '', $date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('request_date', '<=', str_replace(',', '', $date)),
                            );
                    }),
                Filter::make('answer_date')
                    ->form([
                        Forms\Components\Section::make([
                            TextInput::make('min')->label('Min Answer Date')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                ->numeric(),

                            TextInput::make('max')->label('Max Answer Date')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                ->numeric(),
                        ])->columns()
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('answer_date', '>=', str_replace(',', '', $date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('answer_date', '<=', str_replace(',', '', $date)),
                            );
                    }),


            ], getModelFilter())

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('payLoan')
                ->visible(fn($record)=>$record->status->value === "accepted"&& getPeriod())
                ->modalWidth(MaxWidth::FiveExtraLarge)->form([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('number')
                            ->columnSpan(['default' => 8, 'md' => 1, 'lg' => 1, '2xl' => 1, 'xl' => 1])
                            ->default(getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1)->label('Voucher No')->required()->maxLength(255)->readOnly(),
                        Forms\Components\TextInput::make('name')
                            ->columnSpan(['default' => 8, 'md' => 3, 'lg' => 3, '2xl' => 3, 'xl' => 3])
                            ->label('Voucher Title')->required()->maxLength(255),
                        Forms\Components\TextInput::make('reference')
                            ->columnSpan(['default' => 8, 'md' => 1, 'lg' => 1, '2xl' => 1, 'xl' => 1])
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('date')
                            ->columnSpan(['default' => 8, 'md' => 2, 'lg' => 2, '2xl' => 2, 'xl' => 2])
                            ->required()->default(now()),
                    ])->columns(7),
                    Forms\Components\Section::make([
                        SelectTree::make('account_pay')->required()->model(Transaction::class)->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('group','Asset')->where('company_id', getCompany()->id))->searchable(),
                        SelectTree::make('account_resive')->required()->model(Transaction::class)->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('group','Asset')->where('company_id', getCompany()->id))->searchable(),
                    ])->columns(2),

                ])->action(function (array $data, Loan $record): void {
                    // dd($data, $record);


                    $amount = $record->amount;
                    $numberOfInstallments = $record->number_of_installments;
                    $installmentAmount = floor($amount / $numberOfInstallments);
                    $remainder = $amount % $numberOfInstallments;
                    $installments = array_fill(0, $numberOfInstallments, $installmentAmount);
                    if ($remainder > 0) {
                        $installments[0] += $remainder;
                    }

                    // dd($installments, $data, $record);


                    //     "number" => 1
                    //     "name" => "asdf"
                    //     "reference" => null
                    //     "date" => "2025-03-13 16:22:00"
                    //     "account_pay" => null
                    //     "account_resive" => null

                    // $record = [
                    //     "id" => 1,
                    //     "employee_id" => 1,
                    //     "loan_code" => 123,
                    //     "request_amount" => 120000,
                    //     "amount" => 450000,
                    //     "number_of_installments" => 450,
                    //     "number_of_payed_installments" => 2,
                    //     "request_date" => "2025-03-14 00:00:00",
                    //     "answer_date" => "2025-03-25 00:00:00",
                    //     "status" => "waiting",
                    //     "company_id" => 1,
                    //     "created_at" => "2025-03-13 15:37:51",
                    //     "updated_at" => "2025-03-13 15:37:51"
                    // ];


                    $record->update(['status' => 'progressed']);

                    $invoice = Invoice::query()->create([
                        'name' => $data['name'],
                        'number' => $data['number'],
                        'date' => $data['date'],
                        'reference' => $data['reference'],
                        'company_id' =>getCompany()->id,
                    ]);

                    //  pay form bank 
                    $invoice->transactions()->create([
                        'account_id' => $data['account_pay'],
                        'description' => 'Payment of loan amount to employee from bank account',
                        'company_id' => getCompany()->id,
                        'user_id' => auth()->user()->id,
                        'creditor' => str_replace(',', '', $record->amount),
                        'debtor' => 0,
                        "currency_id" =>  defaultCurrency()->id,
                        "exchange_rate" =>  defaultCurrency()->exchange_rate,
                        "debtor_foreign" => 0,
                        "creditor_foreign" => 0,
                        'financial_period_id' => getPeriod()->id,
                    ]);
                    // give loan to employee
                    $invoice->transactions()->create([
                        'account_id' => $data['account_resive'],
                        'description' => 'Loan amount granted to employee for personal use',
                        'company_id' => getCompany()->id,
                        'user_id' => auth()->user()->id,
                        'creditor' => 0,
                        'debtor' => str_replace(',', '', $record->amount),
                        "currency_id" =>  defaultCurrency()->id,
                        "exchange_rate" =>  defaultCurrency()->exchange_rate,
                        "debtor_foreign" => 0,
                        "creditor_foreign" => 0,
                        'financial_period_id' => getPeriod()->id,
                    ]);

                    $firstInstallmentDueDate = Carbon::parse($record->first_installment_due_date);
                    foreach ($installments as $index => $installment) {


                        //give
                        $savedTransaction =  $invoice->transactions()->create([
                            'account_id' => $data['account_resive'],
                            'description' => 'Loan amount granted to employee for personal use',
                            'company_id' => getCompany()->id,
                            'user_id' => auth()->user()->id,
                            'creditor' => $installment,
                            'debtor' => 0,
                            "currency_id" =>  defaultCurrency()->id,
                            "exchange_rate" =>  defaultCurrency()->exchange_rate,
                            "debtor_foreign" => 0,
                            "creditor_foreign" => 0,
                            'financial_period_id' => getPeriod()->id,
                        ]);

                        $chequeDueDate = $firstInstallmentDueDate->copy()->addMonths($index);
                        $savedTransaction->cheque()->create([
                            'type' => 0,
                            'amount' => $installment,
                            'issue_date' => $record->first_installment_due_date,
                            'issue_date' => $firstInstallmentDueDate, 
                            'due_date' => $chequeDueDate, 
                            'description' => 'Cheque for installment payment of loan to employee. Due date: ' . $chequeDueDate->toDateString(), // توضیحات چک
                            'company_id' =>getCompany()->id,
                            'status' => 'pending',
                            'transaction_id' => $savedTransaction->id,  
                        ]);
                    }
                })
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
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }
}
