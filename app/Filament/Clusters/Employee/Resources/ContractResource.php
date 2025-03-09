<?php

namespace App\Filament\Clusters\Employee\Resources;

use App\Filament\Clusters\Employee;
use App\Filament\Clusters\Employee\Resources\ContractResource\Pages;
use App\Filament\Clusters\Employee\Resources\ContractResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'frequency';
    protected static ?string $label='Pay Frequency(Hr Setting)';
    protected static ?string $cluster = HrSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('day')
                    ->required()
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')->alignCenter()
                    ->numeric()
                    ->sortable(),


            ])
            ->filters([
                Filter::make('day')
                    ->form([
                        TextInput::make('min')->label('Min day')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),

                        TextInput::make('max')->label('Max day')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('day', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('day', '<=', str_replace(',','',$date)),
                            );
                    }),
                ],getModelFilter())
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
            'index' => Pages\ListContracts::route('/'),
//            'create' => Pages\CreateContract::route('/create'),
//            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
