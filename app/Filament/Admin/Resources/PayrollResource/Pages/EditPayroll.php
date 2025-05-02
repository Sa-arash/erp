<?php

namespace App\Filament\Admin\Resources\PayrollResource\Pages;

use App\Filament\Admin\Resources\PayrollResource;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
    protected function getRedirectUrl(): ?string
    {
        return route('pdf.payroll',['id'=>$this->record->id]);
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(fn () =>   !$this->data['calculation_done']  ?  "Please Click On Calculate" : "Save Change"  )
            ->submit('save')->disabled(fn () => !$this->data['calculation_done'])
            ->keyBindings(['mod+s']);
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $employee = Employee::query()->firstWhere('id', $this->record->employee_id);
        $this->authorizeAccess();
        $this->fillForm();
        if ($employee) {
            $amount = $employee->base_salary;
            $titleDepartment = $employee->department?->title;
            $titlePosition = $employee->position?->title;
            $salary = $employee->daily_salary;
            $this->data['department'] = $titleDepartment;
            $this->data['position'] = $titlePosition;
            $this->data['salary'] = number_format($salary);
            $this->data['base'] = number_format($amount);
            $this->data['currency'] = $employee->currency?->name;
            $month = Carbon::parse($this->record->start_date)->month-1;
            $year = Carbon::parse($this->record->start_date)->year;
           // dd($this->record);
            $this->data['year']=$year;
            $this->data['month']=$month;
        //    $this->data['calculation_done']=true;

        }

        $this->previousUrl = url()->previous();
    }
}
