<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Employee;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class myEmployees extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()->where('manager_id',auth()->user()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('media.original_url')->state(function ($record) {
                       return $record->media->where('collection_name','images')->first()?->original_url;
                })->disk('public')->defaultImageUrl(fn($record) => $record->gender === "male" ? asset('img/user.png') : asset('img/female.png'))->alignLeft()->label('Profile Picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('fullName')->sortable()->alignLeft()->searchable(),
                Tables\Columns\TextColumn::make('gender')->state(function ($record) {
                    if ($record->gender === "male") {
                        return "Male";
                    } elseif ($record->gender === "female") {
                        return "Female";
                    } else {
                        return "Other";
                    }
                })->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->alignLeft()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('duty.title')->alignLeft()->numeric()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('base_salary')->label('Base Salary' . "(" . defaultCurrency()?->symbol . ")")->alignLeft()->numeric()->sortable()->badge(),
                Tables\Columns\TextColumn::make('department.title')->alignLeft()->color('aColor')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('position.title')->alignLeft()->label('Position')->sortable(),
                Tables\Columns\TextColumn::make('manager.fullName')->alignLeft()->label('Manager')->sortable(),
            ]);
    }
}
