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
    protected static string $relationship = 'purchaseOrder';

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
                Tables\Columns\TextColumn::make('NO')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_orders_number')
                    ->label('PO No')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_po')
                    ->label('Date Of PO')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->state(fn($record) => number_format($record->items->map(fn($item) => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)))?->sum()).$record->currency->symbol)
                    ->searchable(),



                Tables\Columns\TextColumn::make('purchaseRequest.purchase_number')->badge()->url(fn($record) => PurchaseRequestResource::getUrl('index') . "?tableFilters[purchase_number][value]=" . $record->purchaseRequest?->id)
                    ->sortable()->label("PR No"),

                Tables\Columns\TextColumn::make('date_of_delivery')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\Textcolumn::make('status')->badge()
                    ->label('Status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
