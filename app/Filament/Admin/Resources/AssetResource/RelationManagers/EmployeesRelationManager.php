<?php

namespace App\Filament\Admin\Resources\AssetResource\RelationManagers;

use App\Filament\Resources\EmployeeResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';
    protected static ?string $label = "History";
    protected static ?string $title = 'History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
            return $table->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('assetEmployee.employee.fullName')->state(fn($record)=>$record->assetEmployee->employee_id ? $record->assetEmployee->employee->fullName : $record->assetEmployee?->person?->name.'('.$record->assetEmployee?->person?->number.')')->badge()->label('Employee/Person'),
                Tables\Columns\TextColumn::make('warehouse.title')->label('Location'),
                Tables\Columns\TextColumn::make('structure.title')->label('Address'),
                Tables\Columns\TextColumn::make('created_at')->label(' Date')->date(),
                Tables\Columns\TextColumn::make('description')->label('Comment/Note')->wrap(),
                Tables\Columns\TextColumn::make('type')->label('Status')->state(fn($record)=>$record->type ==='Returned' ? "Check In":"Check Out")->badge()->color(fn($record)=>$record->type ==='Returned' ? "success":"danger"),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
