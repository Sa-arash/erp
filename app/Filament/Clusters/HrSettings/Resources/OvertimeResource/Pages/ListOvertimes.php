<?php

namespace App\Filament\Clusters\HrSettings\Resources\OvertimeResource\Pages;

use App\Filament\Clusters\HrSettings\Resources\OvertimeResource;
use App\Models\Employee;
use App\Models\Overtime;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOvertimes extends ListRecords
{
    protected static string $resource = OvertimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->action(function ($data){
                $overtime=Overtime::query()->create([
                    'title'=>$data['title'],
                    'employee_id'=>$data['employee_id'],
                    'company_id'=>getCompany()->id,
                    'user_id'=>auth()->id(),
                    'overtime_date'=>$data['overtime_date'],
                    'hours'=>$data['hours'],
                ]);
                $employee=Employee::query()->firstWhere('id',$data['employee_id']);
                if ($employee->department->employee_id){
                    $overtime->approvals()->create([
                        'position'=>'Head Department',
                        'employee_id'=>$employee->department->employee_id,
                        'company_id'=>getCompany()->id
                    ]);
                }

            } ),
        ];
    }
    public function getTabs(): array
    {
        return [
            'All'=>  Tab::make()->query(fn($query) => $query),
            'approveHead'=>  Tab::make()->query(fn($query) => $query->where('status','approveHead')),
            'Rejected'=>  Tab::make()->query(fn($query) => $query->where('status','rejected')),
            'Pending'=>  Tab::make()->query(fn($query) => $query->where('status','pending')),
            'Approved'=>  Tab::make()->query(fn($query) => $query->where('status','accepted')),
        ];
    }
}
