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
        return $table->defaultSort('asset_employee_id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('assetEmployee.employee.fullName')->url(fn($record)=>EmployeeResource::getUrl('index',['view'=>$record->assetEmployee->employey_id]))->color('aColor')->badge(),
                Tables\Columns\TextColumn::make('warehouse.title')->label('Location'),
                Tables\Columns\TextColumn::make('structure.title')->label('Address'),
                Tables\Columns\TextColumn::make('assetEmployee.date')->label(' Date')->date(),
                Tables\Columns\TextColumn::make('return_date')->date(),
                Tables\Columns\TextColumn::make('return_date')->date(),
                Tables\Columns\TextColumn::make('return_approval_date')->date(),
                Tables\Columns\TextColumn::make('assetEmployee.note')->label('Note')->wrap(),
                Tables\Columns\TextColumn::make('assetEmployee.description')->label('Description')->wrap(),
                Tables\Columns\TextColumn::make('assetEmployee.type')->label('Type')->badge(),
                Tables\Columns\TextColumn::make('type')->label('Status')->state(fn($record)=>$record->return_date ===null ? "Check In":"Check Out")->badge()->color(fn($record)=>$record->return_date ===null ? "success":"danger"),
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
