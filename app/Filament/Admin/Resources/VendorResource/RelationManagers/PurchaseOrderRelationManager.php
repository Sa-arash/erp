<?php

namespace App\Filament\Admin\Resources\VendorResource\RelationManagers;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('product.info')->searchable(query: fn($query,$search)=>$query->whereHas('product',function ($query)use($search){
                    return  $query->where('title','like',"%{$search}%")->orWhere('second_title','like',"%{$search}%")->orWhere('sku','like',"%{$search}%");
                })),
                Tables\Columns\TextColumn::make('description')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('unit.title')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->summarize(Tables\Columns\Summarizers\Sum::make()->numeric()),
                Tables\Columns\TextColumn::make('unit_price')->numeric(2)->label('Unit Price'),
                Tables\Columns\TextColumn::make('taxes')->label('Taxes'),
                Tables\Columns\TextColumn::make('freights')->label('Freights'),
                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor'),
                Tables\Columns\TextColumn::make('currency.name')->label('Currency'),
                Tables\Columns\TextColumn::make('exchange_rate')->label('Exchange Rate'),
                Tables\Columns\TextColumn::make('total')->summarize(Tables\Columns\Summarizers\Sum::make())->label('Total')->numeric(2),
            ])
            ->filters([
                //
            ])
           ;
    }
}
