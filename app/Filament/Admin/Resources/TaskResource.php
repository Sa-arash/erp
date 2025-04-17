<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaskResource\Pages;
use App\Filament\Admin\Resources\TaskResource\RelationManagers;
use App\Filament\Admin\Resources\WarehouseResource\Pages\Inventory;
use App\Models\Task;
use App\Models\TaskReports;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $pluralLabel=' task';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
               Forms\Components\Section::make([
                   Forms\Components\Select::make('employees')->required()->relationship('employees','fullName',modifyQueryUsing: fn($query)=>$query->where('employees.company_id',getCompany()->id))->searchable()->preload()->multiple()->pivotData([
                       'company_id'=>getCompany()->id
                   ])->label('Task Assigned To'),
                   Forms\Components\DateTimePicker::make('start_date')->default(now())->required()->displayFormat('M j, Y h:iAs'),
                   Forms\Components\DateTimePicker::make('deadline')->afterOrEqual(fn(Forms\Get $get)=> $get('start_date'))->required(),
                   Forms\Components\Select::make('priority_level')->searchable()->preload()->options(['Low'=>'Low','Medium'=>'Medium','High'=>'High'])->required(),
               ])->columns(4),
                Forms\Components\Textarea::make('description')->label('Details')->columnSpanFull(),
                MediaManagerInput::make('documents')->orderable(false)->folderTitleFieldName("employee_id")->disk('public')->schema([])->defaultItems(1)->columnSpanFull()->grid(3),
                Forms\Components\Hidden::make('employee_id')->default(getEmployee()?->id)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\IconColumn::make('status')->size(Tables\Columns\IconColumn\IconColumnSize::ExtraLarge),
                Tables\Columns\TextColumn::make('title')->label('Recently Assigned/ Today')->sortable(),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('deadline')->label('Due Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.info')->label('Created By')->sortable(),
                Tables\Columns\TextColumn::make('employees.fullName')->limitList(3)->bulleted()->label('Employees')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('left')->label('Time Left ')->state(function ($record){
                }),
                Tables\Columns\TextColumn::make('start_task')->label('Start Task')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('end_task')->label('End Task')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('priority_level')->color(fn($state)=>$state=='High'?'danger':"warning")->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Assigned Date')->date()->sortable(),
                Tables\Columns\ImageColumn::make('employees.medias')
                    ->circular()
                    ->stacked()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn($record)=>$record->employee_id ===getEmployee()?->id),
                Tables\Actions\DeleteAction::make()->visible(fn($record)=>$record->employee_id ===getEmployee()?->id),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('start_task')->action(function ($record){
                        $record->update(['start_task'=>now()]);
                    })->hidden(fn($record)=>$record->start_task),
                    Tables\Actions\Action::make('Send Reports')->form([
                        Section::make([
                            Textarea::make('description')->columnSpanFull()->required(),
                            FileUpload::make('document')->columnSpanFull()
                        ])->columns()
                    ])->action(function ($record,$data){
                        TaskReports::query()->create([
                            'date'=>now(),
                            'employee_id'=>getEmployee()?->id,
                            'task_id'=>$record->id,
                            'company_id'=>getCompany()->id,
                            'description'=>$data['description'],
                            'document'=>$data['document'],
                        ]);
                        Notification::make('success')->color('success')->success()->title('Submitted Successfully')->send();
                    })->visible(fn($record)=>$record->status->name ==="Processing"),
                    Tables\Actions\Action::make('Done')->color('success')->requiresConfirmation()->action(function ($record){
                        $record->update(['status'=>'Completed','end_task'=>now()]);
                    })->icon('heroicon-o-check-circle')->hidden(fn($record)=>$record->status->name!=="Processing"),
                    Tables\Actions\Action::make('Canceled')->color('danger')->requiresConfirmation()->action(function ($record){
                        $record->update(['status'=>'Canceled']);
                    })->icon('heroicon-o-x-circle')->hidden(fn($record)=>$record->status->name!=="Processing"),
                ])
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
