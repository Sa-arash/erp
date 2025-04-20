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
                Tables\Columns\TextColumn::make('custom_properties')->label('Employee Documents'),
                Tables\Columns\TextColumn::make('original_url')->label('Attachments')->state('Download')->color('aColor')->url(fn($record)=>$record->original_url,true),
                Tables\Columns\TextColumn::make('mime_type')->label('File Format')->formatStateUsing(function ($state){
                    $parts = explode('/', $state);
                    if (count($parts) > 1) {
                        return $parts[1];
                    }
                    return null;
                })
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ;
    }
}
