<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\Payroll;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class MyPayroll extends BaseWidget
{

        protected int | string | array $columnSpan='full';

        public function table(Table $table): Table
        {
            return $table

                ->query(
                    Payroll::query()->where('employee_id',getEmployee()->id)
                )
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

                ->actions([


                    Tables\Actions\Action::make('print')->label('Print')->action(function(Table $table){
                        return redirect(route('pdf.payroll',['id'=>implode('-',$table->getRecords()->pluck('id')->toArray())]));
                    }),
                ]);

        }
    }

