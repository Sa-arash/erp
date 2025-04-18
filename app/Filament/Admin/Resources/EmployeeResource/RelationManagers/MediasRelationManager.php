<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class MediasRelationManager extends RelationManager
{
    protected static string $relationship = 'media';



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('custom_properties')->label('Description'),
                Tables\Columns\TextColumn::make('original_url')->label('Link')->state('Download')->color('aColor')->url(fn($record)=>$record->original_url,true),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ;
    }
}
