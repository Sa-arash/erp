<?php

namespace App\Filament\Resources;

use App\Enums\LoanStatus;
use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?string $navigationIcon = 'loan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->suffixIcon('employee')->suffixIconColor('primary')->label('Employee')->options(Employee::all()->pluck('fullName','id'))->searchable()->preload()
                    ->required(),
                Forms\Components\TextInput::make('loan_code')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('request_amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
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
                Forms\Components\DatePicker::make('answer_date'),
                Forms\Components\ToggleButtons::make('status')->options(LoanStatus::class)->inline()
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
                    ->numeric()
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
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('answer_date')->alignCenter()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->alignCenter(),

            ])
            ->filters([

                SelectFilter::make('employee_id')->searchable()->preload()->options(Employee::all()->pluck('fullName', 'id'))
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
