<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TaskReports;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Task extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Task::query()->whereHas('employees',function ($query){
                    return $query->where('employee_id',getEmployee()->id);
                })->orderByDesc('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.info')->label('Assigned By')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('employees.fullName')->limitList(3)->bulleted()->label('Employees')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Title')->sortable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
                Tables\Columns\TextColumn::make('priority_level')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Assigned Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])->actions([
                Tables\Actions\Action::make('Send Reports')->form([
                    Section::make([
                        Textarea::make('description')->columnSpanFull()->required(),
                        FileUpload::make('document')->columnSpanFull()
                    ])->columns()
                ])->action(function ($record,$data){
                    TaskReports::query()->create([
                        'date'=>now(),
                        'employee_id'=>getEmployee()->id,
                        'task_id'=>$record->id,
                        'company_id'=>getCompany()->id,
                        'description'=>$data['description'],
                        'document'=>$data['document'],
                    ]);
                    Notification::make('success')->color('success')->success()->title('Submitted Successfully')->send();
                })->visible(fn($record)=>$record->status->name ==="Processing"),
                Tables\Actions\Action::make('complete')->requiresConfirmation()->action(function ($record){
                    $record->update(['status'=>'Completed']);
                })
            ]);
    }
}
