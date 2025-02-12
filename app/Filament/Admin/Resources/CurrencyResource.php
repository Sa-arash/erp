<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CurrencyResource\Pages;
use App\Filament\Admin\Resources\CurrencyResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;
    protected static ?string $cluster = FinanceSettings::class;
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $label = 'Currency';
    protected static ?int $navigationSort=4;
    protected static ?string $navigationIcon = 'heroicon-c-currency-dollar';

    public static function getCluster(): ?string
    {
        $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($period) {
            return parent::getCluster();
        }
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('symbol')->required()->maxLength(255),
                Forms\Components\TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                Forms\Components\ToggleButtons::make('is_company_currency')->unique(modifyRuleUsing: function (Unique $rule) {
                    return $rule->where('is_company_currency', 1)->where('company_id',getCompany()->id);
               })->grouped()->label('Base Currency')->default(0)->boolean('Yes','No')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('symbol')->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')->numeric()->sortable()->label('Exchange Rate'),
                Tables\Columns\IconColumn::make('is_company_currency')->boolean()->label('Base Currency'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

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
            'index' => Pages\ListCurrencies::route('/'),
        ];
    }
}
