<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product')
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('product.info')->searchable(query: fn($query,$search)=>$query->whereHas('product',function ($query)use($search){
                 return  $query->where('title','like',"%{$search}%")->orWhere('second_title','like',"%{$search}%");
                })),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('unit.title')->searchable(),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('unit_price')->numeric(2)->label('Unit Price'),
                Tables\Columns\TextColumn::make('taxes')->label('Taxes'),
                Tables\Columns\TextColumn::make('freights')->label('Freights'),
                Tables\Columns\TextColumn::make('total')->label('Total')->numeric(2),
            ])
            ->filters([

            ])
        ;
    }
}
