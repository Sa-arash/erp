<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UrgentTypeleaveResource\Pages;
use App\Filament\Admin\Resources\UrgentTypeleaveResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Models\UrgentTypeleave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UrgentTypeleaveResource extends Resource
{
    protected static ?string $model = UrgentTypeleave::class;

    protected static ?string $cluster = HrSettings::class;
    protected static ?string $pluralLabel='Urgent Leave Type';
    protected static ?string $label='Urgent Leave Type';
    protected static ?string $navigationGroup = 'HR Management System';

    protected static ?string $navigationIcon = 'heroicon-s-arrow-uturn-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              
                    Forms\Components\TextInput::make('title')->label('Leave Title')->maxLength(250)->required(),
                    Forms\Components\TextInput::make('hours')->label('Max Days')->numeric()->required(),
                    Forms\Components\ToggleButtons::make('is_payroll')->inline()->boolean('Paid Leave','Unpaid Leave')->label('Payment')->required(),
                    Forms\Components\Textarea::make('description')->nullable()->maxLength(255)->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Leave Title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('hours')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('is_payroll')->label('Payment')->state(fn($record)=>$record->is_payroll ? "Paid Leave"  :'Unpaid Leave')->alignCenter()->sortable(),
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
            'index' => Pages\ListUrgentTypeleaves::route('/'),
            'create' => Pages\CreateUrgentTypeleave::route('/create'),
            'edit' => Pages\EditUrgentTypeleave::route('/{record}/edit'),
        ];
    }
}
