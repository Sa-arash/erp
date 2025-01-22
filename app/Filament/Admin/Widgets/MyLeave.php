<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyLeave extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::query()->where('employee_id',auth()->user()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('typeLeave.title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->date()->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->tooltip(fn($record) => $record->user?->name)->date()->sortable(),
                Tables\Columns\TextColumn::make('start_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ]);
    }
}
