<?php

namespace App\Filament\Admin\Widgets;

use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Separation extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
               \App\Models\Separation::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('comment')->state('comments')->action(Tables\Actions\Action::make('show')->infolist()) ,

            ])->actions([
                Tables\Actions\Action::make('Approve')->form([
                    TextInput::make('comment')
                ])->action(function ($record,$data){
                    $comments=$record->comments_signature;
                    $comments[]=['employee'=>auth()->user()->employee->id,'comment'=>$data['comment']];
                    $record->update(['comments_signature'=>$comments]);
                })
            ]);
    }
}
