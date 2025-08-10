<?php

namespace App\Filament\Admin\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseRequestItemRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseRequestItem';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('purchaseRequest.purchase_number')->label('PR No')->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('product.sku'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('unit.title'),
                Tables\Columns\TextColumn::make('estimated_unit_cost')->numeric()->label('EST'),
                Tables\Columns\TextColumn::make('total')->state(fn($record)=>$record->quantity*$record->estimated_unit_cost)->numeric()->label('Total EST'),
            ]);
    }
}
