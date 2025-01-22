<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')

        ->columns([
            Tables\Columns\TextColumn::make('')->rowIndex(),
            // Tables\Columns\TextColumn::make('employee.fullName')->alignLeft()->numeric()->sortable(),
            Tables\Columns\TextColumn::make('month')->state(fn($record) => Carbon::parse($record->start_date)->format('M'))->alignLeft()->sortable(),
            Tables\Columns\TextColumn::make('year')->state(fn($record) => Carbon::parse($record->start_date)->year)->alignLeft()->sortable(),
            //   Tables\Columns\TextColumn::make('payment_date')->alignCenter()->state(fn($record) => $record->payment_date ? Carbon::make($record->payment_date)->format('Y/m/d') : "Not Paid")->sortable(),
            Tables\Columns\TextColumn::make('employee.base_salary')->label('Base Salary')->alignLeft()->numeric()->sortable(),
            Tables\Columns\TextColumn::make('total_allowance')->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Allowance'))->label('Total Allowance')->alignLeft()->numeric()->sortable(),
            Tables\Columns\TextColumn::make('total_deduction')->summarize(Tables\Columns\Summarizers\Sum::make('total_deduction')->label('Total Deduction'))->label('Total Deduction')->alignLeft()->numeric()->sortable(),
            Tables\Columns\TextColumn::make('amount_pay')->summarize(Tables\Columns\Summarizers\Sum::make('amount_pay')->label('Total Net Pay'))->label('Net Pay')->alignLeft()->numeric()->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->alignLeft(),
        ])

            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([

                // Tables\Actions\CreateAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('print')->label('Print')->action(function(Table $table){
                    return redirect(route('pdf.payrolls',['ids'=>implode('-',$table->getRecords()->pluck('id')->toArray())]));
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
