<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use App\Filament\Admin\Resources\EmployeeResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable(),
                Tables\Columns\TextColumn::make('product.title')->label('Asset Name')->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Purchase Price ')->sortable()->numeric(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('warehouse.title')->sortable(),
                Tables\Columns\TextColumn::make('structure.title')->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('employee')->state(function ($record){
                    return  $record->employees->last()?->assetEmployee?->employee?->fullName;
                })->badge()->url(function($record){
                    if ($record->employees->last()?->assetEmployee?->employee_id){
                        return EmployeeResource::getUrl('view',['record'=>$record->employees->last()?->assetEmployee?->employee_id]);
                    }
                })->label('Employee'),
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
