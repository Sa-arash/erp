<?php

namespace App\Filament\Admin\Resources\GrnResource\Pages;

use App\Filament\Admin\Resources\GrnResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGrn extends CreateRecord
{
    protected static string $resource = GrnResource::class;

    protected function afterCreate()
    {
        $this->record->purchaseOrder->update(['status'=>"GRN"]);
        if ($this->record->purchaseOrder?->purchaseRequest?->employee_id){
            $this->record->approvals()->create([
                'employee_id'=>$this->record->purchaseOrder?->purchaseRequest?->employee_id,
                'position'=>"Requester",
                'company_id'=>$this->record->company_id
            ]);
        }
    }
}
