<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use App\Models\Employee;
use App\Models\Overtime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OverTimesRelationManager extends RelationManager
{
    protected static string $relationship = 'overTimes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->label('Employee')->searchable()->preload()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->required(),
                Forms\Components\TextInput::make('title')->label('description')->required()->maxLength(255),
                Forms\Components\DatePicker::make('overtime_date')->required(),
                Forms\Components\TextInput::make('hours')->numeric()->required()

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('description')->searchable(),
                Tables\Columns\TextColumn::make('overtime_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge(),
            ])->headerActions([
                Action::make('Request Overtime')
                    ->form([
                        Forms\Components\TextInput::make('title')->label('description')->required()->maxLength(255),
                        Forms\Components\DatePicker::make('overtime_date')->required(),
                        Forms\Components\TextInput::make('hours')->numeric()->required()

                    ])
                    ->action(function (array $data,  $record): void {
                        $data['company_id'] = getCompany()->id;
                        $data['employee_id'] = Employee::query()->firstWhere('user_id', auth()->user()->id)->id;
                        Overtime::query()->create($data);
                    })
            ])
            ->filters([
                //
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
