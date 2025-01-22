<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Overtime;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyOvertime extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                 Overtime::query()->where('employee_id',auth()->user()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('description')->searchable(),
                Tables\Columns\TextColumn::make('overtime_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge(),
            ]);
    }
}
