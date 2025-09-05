<?php

namespace App\Filament\Clusters\Leave\Resources\LeaveResource\Pages;

use App\Filament\Clusters\Leave\Resources\LeaveResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;
    public function afterCreate(){
        $employee=Employee::query()->firstWhere('id',$this->record->employee_id);
        sendAR($employee,$this->record,getCompany());

    }
}
