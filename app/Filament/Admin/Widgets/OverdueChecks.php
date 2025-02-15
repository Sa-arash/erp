<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Cheque;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class OverdueChecks extends BaseWidget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 2;

    protected static ?string $chartId = 'OverdueChecks';

    protected static ?string $heading = 'Overdue Checks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cheque::query()
                ->where('status', 'pending')
                ->whereDate('due_date', '<=', Carbon::now()->addDays(3)) 
                ->whereDate('due_date', '>=', Carbon::now())
            )
            ->columns([
                    Tables\Columns\TextColumn::make('cheque_number')->searchable(),
                    Tables\Columns\TextColumn::make('payer_name')->searchable(),
                    Tables\Columns\TextColumn::make('payee_name')->searchable(),
                    Tables\Columns\TextColumn::make('type')->state(fn($record) => $record->type ? "Payable" : "Receivable")->badge(),
                    Tables\Columns\TextColumn::make('bank_name')->searchable(),
                    Tables\Columns\TextColumn::make('branch_name')->searchable(),
                    Tables\Columns\TextColumn::make('account_number')->searchable(),
                    Tables\Columns\TextColumn::make('amount')->numeric()->sortable(),
                    Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                    Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                    Tables\Columns\TextColumn::make('status')->badge(),
    
            ]);
    }
}
