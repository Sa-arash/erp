<?php

namespace App\Filament\Admin\Resources\ExpenseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('transactionable.title')->searchable()->label('Title')->alignCenter(),
//                Tables\Columns\TextColumn::make('transactionable.name')->state(fn($record)=>$record->transactionable_type =="App\Models\Expense"  ?   :)->label('Vendor/Customer   ')->alignCenter(),
                Tables\Columns\TextColumn::make('amount_pay')->numeric()->tooltip(fn($record)=>"Balance Amount : ".$record->balance_amount)->badge()->color(fn($record)=>$record->transactionable_type === "App\Models\Expense" ?  'danger' :"success" )->label('Amount')->alignCenter(),
                Tables\Columns\TextColumn::make('payment_date')->dateTime()->label('Payment Date')->alignCenter(),
//                Tables\Columns\TextColumn::make('user.name')->label('User')->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->filters([
                Tables\Filters\Filter::make('filter')->form([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('minAmount')->columnSpan(1)->label('Min Amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->numeric(),
                        Forms\Components\TextInput::make('maxAmount')->columnSpan(1)->label('Max Amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->numeric(),

                    ])->columns(4)
                ])->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['minAmount'],
                            fn(Builder $query, $date): Builder => $query->where('amount_pay', '>=', str_replace(',','',$date)),
                        )
                        ->when(
                            $data['maxAmount'],
                            fn(Builder $query, $date): Builder => $query->where('amount_pay', '<=', str_replace(',','',$date)),
                        );
                })->columnSpanFull(),
                DateRangeFilter::make('payment_date'),
//                Tables\Filters\SelectFilter::make('user_id')->options()
            ],getModelFilter())
            ->actions([


            ])
            ->bulkActions([

            ]);
    }
}
