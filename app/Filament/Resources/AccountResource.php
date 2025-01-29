<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use App\Models\Company;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-c-user-group';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $label = 'Bank Account';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('holder_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('account_number')->required()->numeric()->maxLength(255),
                Forms\Components\TextInput::make('bank_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('branch')->required()->maxLength(255),
                Forms\Components\TextInput::make('swift_code')->required()->maxLength(255),
                Forms\Components\Select::make('currency')->required()->options(getCurrency())->searchable()->live(),
                Forms\Components\TextInput::make('initial_balance')->disabled(fn(string $operation): bool => $operation === 'edit')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->maxLength(255),
                Forms\Components\TextInput::make('contact_number')->required()->numeric()->maxLength(255),
                Forms\Components\Select::make('company_id')->columnSpanFull()->label('Company')->searchable()->preload()->options(Company::query()->pluck('title','id'))->required(),
                Forms\Components\Textarea::make('address')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('holder_name')->searchable(    ),
                Tables\Columns\TextColumn::make('bank_name')->searchable(),
                Tables\Columns\TextColumn::make('account_number')->searchable(),
                Tables\Columns\TextColumn::make('amount')->badge()->money(fn($record)=> $record->currency)->sortable(),
                Tables\Columns\TextColumn::make('contact_number')->searchable(),
                Tables\Columns\TextColumn::make('branch')->searchable(),
                Tables\Columns\TextColumn::make('swift_code')->searchable(),
                Tables\Columns\TextColumn::make('company.title')->searchable(),
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
