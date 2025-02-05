<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVisitorRequest extends CreateRecord
{
    protected static string $resource = VisitorRequestResource::class;

    public function afterCreate(){
        if ($this->record->employee?->department?->employee_id){
            $this->record->approvals()->create([
                'employee_id'=>$this->record->employee->department->employee_id,
                'company_id'=>$this->record->company_id,
                'position'=>'VisitAccessRequest'
            ]);
        }
    }
}
