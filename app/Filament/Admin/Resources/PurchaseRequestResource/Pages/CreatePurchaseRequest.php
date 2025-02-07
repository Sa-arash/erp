<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;
    public function afterCreate(){
        $request=$this->record;
        $company=getCompany();
        $employee=Employee::query()->firstWhere('id',$request->employee_id);
        sendAR($employee,$request,$company);

    }
}
