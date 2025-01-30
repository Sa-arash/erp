<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UnitResource\Pages;
use App\Filament\Admin\Resources\UnitResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    protected static ?string $navigationIcon = 'heroicon-c-server-stack';
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $cluster = StackManagementSettings::class;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Unit Name')->unique('units','title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_package')->live()
                    ->required(),
                Forms\Components\TextInput::make('items_per_package')
                    ->numeric()->visible(fn(Get $get)=>$get('is_package'))
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Unit Name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_package')
                    ->boolean(),
                Tables\Columns\TextColumn::make('items_per_package')
                    ->numeric()
                    ->sortable(),
                    // Tables\Columns\TextColumn::make('Product')->color('aColor')->alignCenter()->state(fn($record)=> $record->products->count())
                    // ->url(fn($record)=>ProductResource::getUrl().'?tableFilters[unit_id][value]='.$record->id),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
           // 'create' => Pages\CreateUnit::route('/create'),
           // 'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
