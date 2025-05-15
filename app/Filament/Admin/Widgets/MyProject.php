<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\TaskResource;
use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyProject extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                return Project::query()
                    ->where('employee_id',getEmployee()->id)
                    ->orWhereJsonContains('members', (string) getEmployee()->id);
            })
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->badge()->label('Manager')->sortable(),
                Tables\Columns\TextColumn::make('priority_level')->badge(),
            ])->actions([
                Tables\Actions\Action::make('view')->label('View Tasks')->url(fn($record)=>TaskResource::getUrl('index',['tableFilters[project_id][value]'=>$record->id]))
            ]);
    }
}
