<?php

namespace App\Filament\Admin\Resources\WarehouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('price')->label('Purchase Price')->numeric()->summarize(Tables\Columns\Summarizers\Sum::make()),
                Tables\Columns\TextColumn::make('buy_date'),

            ])
            ->filters([
                //
            ])
           ;
    }
}
