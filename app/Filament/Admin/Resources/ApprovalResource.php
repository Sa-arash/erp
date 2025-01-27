<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Filament\Admin\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('approvable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('approvable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('approve_date'),
                Forms\Components\Textarea::make('comment')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'id')
                    ->required(),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'title')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('approvable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('approvable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approve_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('employee.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.title')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListApprovals::route('/'),
            'create' => Pages\CreateApproval::route('/create'),
            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
