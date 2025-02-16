<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PositionResource\Pages;
use App\Filament\Admin\Resources\PositionResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Forms\Components\Permission;
use App\Models\Position;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;
    protected static ?string $cluster = HrSettings::class;
    protected static ?int $navigationSort=-2;
    protected static ?string $label='Designation';
    protected static ?string $navigationIcon = 'position';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->unique(ignoreRecord: true,modifyRuleUsing: function (Unique $rule) {
                    return $rule->where('company_id', getCompany()->id);
                })->label('Designation Title')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('document')->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Designation Title')
                    ->sortable(),
                   Tables\Columns\TextColumn::make('employees')->color('aColor')->alignCenter()->state(fn($record)=> $record->employees->count())->sortable()
                   ->url(fn($record)=>EmployeeResource::getUrl().'?tableFilters[position_id][value]='.$record->id),



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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
