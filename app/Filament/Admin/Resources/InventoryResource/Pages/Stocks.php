<?php

namespace App\Filament\Admin\Resources\InventoryResource\Pages;

use App\Filament\Admin\Resources\InventoryResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class Stocks extends ManageRelatedRecords
{
    protected static string $resource = InventoryResource::class;

    protected static string $relationship = 'stocks';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Stocks';
    }



    public function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')->heading(fn()=>$this->record->product->info)
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->badge(),
                Tables\Columns\TextColumn::make('package.title')->state(fn($record)=> isset($record->package?->quantity)? '('.$record->quantity /$record->package?->quantity.' * '. $record->package?->quantity .')'.$record->package->title:'---')->badge(),
                Tables\Columns\TextColumn::make('type')->state(fn($record) => $record->type === 1 ? "Stock In" : "Stock Out")->badge()->color(fn($state) => $state === "Stock In" ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('transaction')->state(function($record){
                    if ($record->transaction){
                        return  $record->type ?"Stock In" : "Stock Out";
                    }

                } )->badge()->color(fn($state) => $state === "Stock In" ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')->label('Stock Date')->dateTime(),
            ])
            ->filters([
                DateRangeFilter::make('created_at')->label('Stock Date'),
                Tables\Filters\TernaryFilter::make('type')->label('Type')->placeholder('All Type')->trueLabel('Stock In')->falseLabel('Stock Out')->searchable(),
                Tables\Filters\TernaryFilter::make('transaction')->label('Transaction')->placeholder('All Stocks')->trueLabel('Yes')->falseLabel('No')->searchable()
            ],getModelFilter())

            ->bulkActions([

            ]);
    }
}
