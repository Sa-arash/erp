<?php

namespace App\Filament\Admin\Resources\TaskResource\Pages;

use App\Filament\Admin\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'Assigned Tasks'=>  Tab::make()->query(fn($query) => $query->where('employee_id',getEmployee()->id)),
            'My Tasks'=>  Tab::make()->query(fn($query) => $query->whereHas('employees',function ($query){
                $query->where('employee_id',getEmployee()->id);
            })),
        ];
    }
}
