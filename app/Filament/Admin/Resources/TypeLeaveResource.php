<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TypeLeaveResource\Pages;
use App\Filament\Admin\Resources\TypeLeaveResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Filament\Clusters\Leave;
use App\Models\Typeleave;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeLeaveResource extends Resource
{
    protected static ?string $model = Typeleave::class;
    protected static ?string $cluster = HrSettings::class;
    protected static ?string $label='Leave Type';

    protected static ?string $navigationIcon = 'heroicon-s-arrow-uturn-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Leave Title')->maxLength(250)->required(),
                Forms\Components\TextInput::make('days')->label('Max Days')->numeric()->required(),
                Forms\Components\ToggleButtons::make('is_payroll')->inline()->boolean('Paid Leave','Unpaid Leave')->label('Payment')->required(),
                Forms\Components\Textarea::make('description')->nullable()->maxLength(255)->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Leave Title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('days')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('is_payroll')->label('Payment')->state(fn($record)=>$record->is_payroll ? "Paid Leave"  :'Unpaid Leave')->alignCenter()->sortable(),
            ])
            ->filters([

                TernaryFilter::make('is_payroll'),

                Filter::make('days')
                    ->form([
                        TextInput::make('min')->label('Min Days')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),

                        TextInput::make('max')->label('Max Days')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('days', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('days', '<=', str_replace(',','',$date)),
                            );
                    }),


            ], getModelFilter())
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
            'index' => Pages\ListTypeLeaves::route('/'),
//            'edit' => Pages\EditTypeLeave::route('/{record}/edit'),
        ];
    }
}
