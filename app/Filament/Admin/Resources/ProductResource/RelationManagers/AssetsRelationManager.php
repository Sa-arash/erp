<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('number')->state(fn() => '_______________')->label('Barcode')->searchable()->description(function ($record) {
                    $barcode = '<img  src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($record->number, 'C39', 1, 20) . '" alt="barcode"/>';
                    $barcode .= "<p style='text-align: center'>{$record->number}</p>";
                    return new HtmlString($barcode);
                })->width(100)->action(function ($record) {
                    return redirect(route('pdf.barcode', ['code' => $record->number]));
                }),
                Tables\Columns\TextColumn::make('description')->label('Asset Description')->searchable(),
                Tables\Columns\TextColumn::make('employee')->state(function ($record) {
                    return $record->check_out_to ? $record?->checkOutTo?->fullName : $record?->person?->name . ' (' . $record?->person?->number . ')';
                })->badge()->label('Custodian'),
                Tables\Columns\TextColumn::make('brand.title'),
                Tables\Columns\TextColumn::make('status')->state(fn($record) => match ($record->status) {
                    'inuse' => "In Use",
                    'inStorageUsable' => "In Storage",
                    'loanedOut' => "Loaned Out",
                    'outForRepair' => 'Out For Repair',
                    'StorageUnUsable' => " Scrap"
                })->badge(),
                Tables\Columns\TextColumn::make('warehouse.title')->sortable(),
                Tables\Columns\TextColumn::make('structure.title')->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('price')->summarize(Tables\Columns\Summarizers\Sum::make())->label('Purchase Price ')->sortable()->numeric(),


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
