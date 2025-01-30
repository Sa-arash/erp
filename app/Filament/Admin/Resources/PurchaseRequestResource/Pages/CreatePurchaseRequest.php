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
        if ($employee->department->employee_id) {
            if ($employee->department->employee_id === $employee->id) {
                $request->approvals()->create([
                    'employee_id' => $employee->department->employee_id,
                    'company_id' => $company->id,
                    'position' => 'Head  Of Department',
                    'status' => "Approve",
                    'approve_date' => now()
                ]);
                $request->update(['status' => 'FinishedHead']);
                $CEO = Employee::query()->firstWhere('user_id', $company->user_id);
                $request->approvals()->create([
                    'employee_id' => $CEO->id,
                    'company_id' => $company->id,
                    'position' => 'CEO',
                    'status' => "Pending"
                ]);

            } else {
                $request->approvals()->create([
                    'employee_id' => $employee->department->employee_id,
                    'company_id' => $company->id,
                    'position' => 'Head Of Department',

                ]);
            }
        }
    }
}
