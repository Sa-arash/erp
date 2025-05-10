<?php

namespace App\Filament\Admin\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';



    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id','desc')
            ->columns([

                Tables\Columns\IconColumn::make('status')->size(Tables\Columns\IconColumn\IconColumnSize::ExtraLarge),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Created By')->sortable(),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date (M/D/Y  /H)')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('deadline')->label('Due Date (M/D/Y /H)')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Created By')->sortable(),
                Tables\Columns\TextColumn::make('left')->label('Time Left ')->state(function ($record){
                    $startDateTime = now()->format('Y-m-d H:i:s');
                    $endDateTime = $record->deadline;
                    $difference = calculateTimeDifference($startDateTime, $endDateTime);
                    return $difference;
                }),
                Tables\Columns\TextColumn::make('start_task')->label('Start Task')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('end_task')->label('End Task ')->dateTime()->sortable(),
                Tables\Columns\ImageColumn::make('employees.medias')->state(function ($record){
                    $data=[];
                    foreach ($record->employees as $employee){
                        if ($employee->media->where('collection_name','images')->first()?->original_url){
                            $data[]= $employee->media->where('collection_name','images')->first()?->original_url;
                        } else {
                            $data[] = $employee->gender === "male" ? asset('img/user.png') : asset('img/female.png');
                        }
                    }
                    return $data;
                })
                    ->circular()
                    ->stacked(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->label('Task Assigned')->searchable()->preload()->relationship('employees', 'fullName', modifyQueryUsing: fn($query) => $query->where('employees.company_id', getCompany()->id)),
                Tables\Filters\SelectFilter::make('employees')->label('Employees')->searchable()->preload()->relationship('employees', 'fullName', modifyQueryUsing: fn($query) => $query->where('employees.company_id', getCompany()->id)),
            ],getModelFilter());
    }
}
