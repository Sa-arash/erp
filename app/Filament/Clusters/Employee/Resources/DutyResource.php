<?php

namespace App\Filament\Clusters\Employee\Resources;

use App\Filament\Clusters\Employee;
use App\Filament\Clusters\Employee\Resources\DutyResource\Pages;
use App\Filament\Clusters\Employee\Resources\DutyResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Filament\Resources\EmployeeResource;
use App\Models\Duty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DutyResource extends Resource
{
    protected static ?string $model = Duty::class;

    protected static ?string $navigationIcon = 'duty';
    protected static ?string $label = 'Duty Type (HR Setting)';
    protected static ?string $pluralLabel = 'Duty Type';
    protected static ?string $cluster = HrSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->sortable(),
                Tables\Columns\TextColumn::make('employees')->alignCenter()->state(fn($record)=> $record->employees->count())->sortable()
                ->url(fn($record)=>EmployeeResource::getUrl().'?tableFilters[duty_id][value]='.$record->id),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modelLabel('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDuties::route('/'),
//            'create' => Pages\CreateDuty::route('/create'),
//            'edit' => Pages\EditDuty::route('/{record}/edit'),
        ];
    }
}
