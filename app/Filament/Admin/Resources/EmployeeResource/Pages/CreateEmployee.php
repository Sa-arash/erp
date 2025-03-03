<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Models\CompanyUser;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorType;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
    use CreateRecord\Concerns\HasWizard;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Section::make([
                    Wizard::make(EmployeeResource::getForm())
                        ->startOnStep($this->getStartStep())
                        ->submitAction($this->getSubmitFormAction())
                        ->skippable($this->hasSkippableSteps())
                        ->contained(false)->columnSpanFull(),
                ])
            ])
            ->columns(null);
    }
    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');



            $data = $this->mutateFormDataBeforeCreate($data);

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
        } catch (\Throwable $exception) {
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

}
