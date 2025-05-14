<?php

namespace App\Filament\Admin\Resources\AssetResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceRelationManager extends RelationManager
{
    protected static string $relationship = 'service';

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
            ->recordTitleAttribute('title')->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('request_date')->label('Request Date')->dateTime(),
                Tables\Columns\TextColumn::make('type')->label('Type Service')->badge(),
                Tables\Columns\TextColumn::make('note')->label('Not'),
                Tables\Columns\TextColumn::make('answer_date')->label('Answer Date')->dateTime(),
                Tables\Columns\TextColumn::make('reply')->label('Reply'),
                Tables\Columns\TextColumn::make('service_date')->label('Service Date')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
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
