<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LoanStatus;
use App\Filament\Admin\Resources\LoanResource\Pages;
use App\Filament\Admin\Resources\LoanResource\RelationManagers;
use App\Models\Employee;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                                fn(Builder $query, $date): Builder => $query->where('request_date', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('request_date', '<=', str_replace(',','',$date)),
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
                                fn(Builder $query, $date): Builder => $query->where('answer_date', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('answer_date', '<=', str_replace(',','',$date)),
                            );
                    }),


            ], getModelFilter())

            ->actions([
                Tables\Actions\EditAction::make(),
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
