<?php

namespace App\Filament\Admin\Widgets;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TakeOut extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\TakeOut::query()->where('employee_id', getEmployee()->id)->orderBy('id','desc')
            )->headerActions([
            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('from'),
                Tables\Columns\TextColumn::make('to'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
            ])->actions([
                Tables\Actions\Action::make('pdf')->url(fn($record) => route('pdf.takeOut', ['id' => $record->id]))->icon('heroicon-s-printer')->iconSize(IconSize::Large)->label('PDF'),
                Tables\Actions\Action::make('view')->infolist([
                    Section::make([
                        TextEntry::make('employee.fullName'),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('date')->date(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('type')->badge(),

                        RepeatableEntry::make('items')->label('Assets')->schema([
                            TextEntry::make('asset.product.title'),
                            TextEntry::make('remarks'),
                            TextEntry::make('returned_date'),
                        ])->columnSpanFull()->columns(3)
                    ])->columns()
                ])
            ]);


    }


}
