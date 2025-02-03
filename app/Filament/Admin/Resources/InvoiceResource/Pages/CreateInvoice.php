<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\InvoiceResource;
use App\Models\FinancialPeriod;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Throwable;

use function Filament\Support\is_app_url;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
    public function mount(): void
    {
        if((getCompany()->financialPeriods->firstWhere('status', "During")===null) )
        {
            $this->redirect(FinancialPeriodResource::getUrl('index'));
        }
        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }
    
    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');
            $debtor=0;
            $creditor=0;
            foreach ($this->data['transactions'] as $transaction){
                if ($transaction['creditor'] >0){
                    $creditor+=str_replace(',','',$transaction['creditor']);
                }else{
                    $debtor+=str_replace(',','',$transaction['debtor']);

                }
            }
            if ($debtor !== $creditor){
                 Notification::make('warning')->title('Creditor and Debtor not equal')->warning()->send();
                return;
            }


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

}
