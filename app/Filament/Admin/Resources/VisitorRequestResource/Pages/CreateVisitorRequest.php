<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use App\Models\Employee;
use App\Models\VisitorRequest;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreateVisitorRequest extends CreateRecord
{
    protected static string $resource = VisitorRequestResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);
            $employee=Employee::query()->with(['department','department.employees'])->firstWhere('id',$data['requested_by']);
            $abr=$employee?->department?->abbreviation;
            $lastRecord=VisitorRequest::query()->whereIn('requested_by',$employee->department->employees()->pluck('id'))->latest()->first();
            if ($lastRecord){
                $code=getNextCodeVisit($lastRecord->SN_code,$abr);
            }else{
                $code=$abr."/00001";
            }

            $data['SN_code']=$code;
            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    public function afterCreate(){
        sendSecurity($this->record, getCompany());
        // sendAdmin($this->record->employee,$this->record,getCompany());
    }
}
