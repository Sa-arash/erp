<?php

namespace App\Filament\Admin\Resources\GrnResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsGrnRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

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
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('receive_status')->color(fn($state) => match ($state) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default=>"primary"
                })->badge(),
                Tables\Columns\TextColumn::make('receive_comment')->wrap(),
                Tables\Columns\TextColumn::make('total')->summarize(Tables\Columns\Summarizers\Sum::make()->numeric())->label('Total')->numeric(2),
            ]) ;
    }
}
