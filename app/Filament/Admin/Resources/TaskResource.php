<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaskResource\Pages;
use App\Filament\Admin\Resources\TaskResource\RelationManagers;
use App\Filament\Exports\EmployeeExporter;
use App\Filament\Exports\TaskExporter;
use App\Models\Task;
use App\Models\TaskReports;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Jobs\PrepareCsvExport;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
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
                    Forms\Components\Select::make('employees')->required()->relationship('employees', 'fullName', modifyQueryUsing: fn($query) => $query->where('employees.company_id', getCompany()->id))->searchable()->preload()->multiple()->pivotData([
                        'company_id' => getCompany()->id
                    ])->label('Task Assigned To'),
                    Forms\Components\DateTimePicker::make('start_date')->default(now())->required(),
                    Forms\Components\DateTimePicker::make('deadline')->afterOrEqual(fn(Forms\Get $get) => $get('start_date'))->required(),
                    Forms\Components\Select::make('priority_level')->searchable()->preload()->options(['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High'])->required(),
                ])->columns(4),
                Forms\Components\Textarea::make('description')->label('Details')->columnSpanFull(),
                MediaManagerInput::make('documents')->orderable(false)->folderTitleFieldName("employee_id")->disk('public')->schema([])->defaultItems(0)->columnSpanFull()->grid(3),
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
                Tables\Columns\TextColumn::make('employee.fullName')->label('Created By')->sortable(),
                Tables\Columns\TextColumn::make('left')->label('Time Left ')->state(function ($record){
                    $startDateTime = now()->format('Y-m-d H:i:s');
                    $endDateTime = $record->deadline;
                    $difference = calculateTimeDifference($startDateTime, $endDateTime);
                    return $difference;
                }),
                Tables\Columns\TextColumn::make('start_task')->label('Start Task')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('end_task')->label('End Task')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('priority_level')->label('Priority Level')->color(fn($state)=>$state=='High'?'danger':"warning")->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Assigned Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employees.fullName')->limitList(3)->bulleted()->label('Employees')->numeric()->sortable(),
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
                    ->stacked()
            ])
            ->filters([
                DateRangeFilter::make('start_date'),
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('Duplicate')->iconSize(IconSize::Large)->icon('heroicon-o-clipboard-document-check')->label('Duplicate')->url(fn($record) => TaskResource::getUrl('replicate', ['id' => $record->id])),
                Tables\Actions\ViewAction::make()->modalWidth(MaxWidth::Full),
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->employee_id === getEmployee()?->id),
                Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->employee_id === getEmployee()?->id),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('start_task')->iconSize(IconSize::Medium)->icon('heroicon-o-clock')->color('success')->action(function ($record) {
                        $record->update(['start_task' => now()]);
                    })->hidden(fn($record) => $record->start_task),
                    Tables\Actions\Action::make('Send Reports')->icon('heroicon-c-paper-clip')->color('warning')->form([
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
                Tables\Actions\ExportBulkAction::make()->label('Export Task')->color('purple')->exporter(TaskExporter::class)  ->formats([
                    ExportFormat::Xlsx,
                ]),
                    Tables\Actions\BulkAction::make('print')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->action(function ($records) {
                        return redirect(route('pdf.tasks', ['ids' => implode('-', $records->pluck('id')->toArray())]));
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReportRelationManager::class
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return Task::query()->where('company_id',getCompany()->id)->whereHas('employees',function ($query){
            $query->where('employee_id',getEmployee()->id);
        })->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
            'view' => Pages\ViewTask::route('/{record}/view'),
            'replicate' => Pages\ReplicateTask::route('/{id}/replicate')
        ];
    }
}
