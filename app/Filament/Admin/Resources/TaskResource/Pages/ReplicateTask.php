<?php

namespace App\Filament\Admin\Resources\TaskResource\Pages;

use App\Filament\Admin\Resources\TaskResource;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Task;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class ReplicateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
    protected static string $model = Task::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Section::make([
                    Select::make('employees')->required()->relationship('employees','fullName',modifyQueryUsing: fn($query)=>$query->where('employees.company_id',getCompany()->id))->searchable()->preload()->multiple()->pivotData([
                        'company_id'=>getCompany()->id
                    ])->label('Task Assigned To'),
                    DateTimePicker::make('start_date')->default(now())->required(),
                    DateTimePicker::make('deadline')->afterOrEqual(fn(Get $get)=> $get('start_date'))->required(),
                    Select::make('priority_level')->searchable()->preload()->options(['Low'=>'Low','Medium'=>'Medium','High'=>'High'])->required(),
                ])->columns(4),
               Textarea::make('description')->label('Details')->columnSpanFull(),
                MediaManagerInput::make('documents')->orderable(false)->folderTitleFieldName("employee_id")->disk('public')->schema([])->defaultItems(0)->columnSpanFull()->grid(3),
                Hidden::make('employee_id')->default(getEmployee()?->id)->required(),
            ]);
    }


    public function afterFill()
    {
        $task = Task::query()->with(['media','employees'])->firstWhere('id', request('id'));
//        $media=$task->media->all();
//        $medias=[];
//        foreach ($media as $item){
//            $medias[]=$item->getUrl();
//        }
        if (!$task) {
            abort(404);
        }
        $task = $task->toArray();
        $task['deadline']=null;
        $task['start_task']=null;
        $task['end_task']=null;
        $task['status']='Processing';
        foreach ($task['employees'] as $key=>$employee){
            $task['employees'][$key]=$employee['id'];
        }
        $task['documents']=[];
        $task['start_date']=now()->format('Y-m-d H:i:s');
        $this->data = $task;
    }
}
