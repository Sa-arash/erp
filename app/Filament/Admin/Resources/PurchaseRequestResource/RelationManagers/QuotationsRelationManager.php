<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('purchase_request_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('party_id')
                    ->relationship('party', 'name')
                    ->required(),
                Forms\Components\DateTimePicker::make('date')
                    ->required(),
                Forms\Components\Textarea::make('file')
                    ->columnSpanFull(),
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'id'),
                Forms\Components\Select::make('employee_operation_id')
                    ->relationship('employeeOperation', 'id'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
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
