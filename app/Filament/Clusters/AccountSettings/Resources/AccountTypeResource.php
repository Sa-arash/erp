<?php

namespace App\Filament\Clusters\AccountSettings\Resources;

use App\Filament\Clusters\AccountSettings;
use App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource\Pages;
use App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource\RelationManagers;
use App\Models\AccountType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountTypeResource extends Resource
{
    protected static ?string $model = AccountType::class;
    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function canAccess(): bool
    {
        return false;
    }
    protected static ?string $cluster = AccountSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->columnSpanFull()->maxLength(255)->required(),
                Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->alignCenter(),
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
            'index' => Pages\ListAccountTypes::route('/'),
//            'create' => Pages\CreateAccountType::route('/create'),
//            'edit' => Pages\EditAccountType::route('/{record}/edit'),
        ];
    }
}
