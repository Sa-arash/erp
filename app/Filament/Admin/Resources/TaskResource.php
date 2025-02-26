<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaskResource\Pages;
use App\Filament\Admin\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\TaskReports;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;


    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
               Forms\Components\Section::make([
                   Forms\Components\Select::make('employees')->required()->relationship('employees','fullName',modifyQueryUsing: fn($query)=>$query->where('employees.company_id',getCompany()->id))->searchable()->preload()->multiple()->pivotData([
                       'company_id'=>getCompany()->id
                   ]),
                   Forms\Components\DatePicker::make('start_date')->default(now())->required(),
                   Forms\Components\DatePicker::make('deadline')->afterOrEqual(fn(Forms\Get $get)=> $get('start_date'))->required(),
                   Forms\Components\Select::make('priority_level')->searchable()->preload()->options(['Low'=>'Low','Medium'=>'Medium','High'=>'High'])->required(),
               ])->columns(4),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\FileUpload::make('document')->downloadable()->columnSpanFull(),
                Forms\Components\Hidden::make('employee_id')->default(getEmployee()?->id)->required(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Title')->sortable(),
                Tables\Columns\TextColumn::make('employee.info')->label('Assigned By')->sortable(),
                Tables\Columns\TextColumn::make('employees.fullName')->limitList(3)->bulleted()->label('Employees')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
                Tables\Columns\TextColumn::make('priority_level')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Assigned Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn($record)=>$record->employee_id ===getEmployee()?->id),
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
                Tables\Actions\Action::make('Done')->color('success')->requiresConfirmation()->action(function ($record){
                    $record->update(['status'=>'Completed']);
                }),
                Tables\Actions\Action::make('Canceled')->color('danger')->requiresConfirmation()->action(function ($record){
                    $record->update(['status'=>'Canceled']);
                }),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
