<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;


    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState(afterValidate: function () {
                $this->callHook('afterValidate');

                $this->callHook('beforeSave');
            });

            $data = $this->mutateFormDataBeforeSave($data);
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
            $this->handleRecordUpdate($this->getRecord(), $data);

            $this->callHook('afterSave');

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

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();
        foreach ($this->data['transactions']  as $key=> $datum){

            if ($datum['cheque']['due_date']){
                $this->data['transactions'][$key]['Cheque']=true;
            }
        }

        $this->previousUrl = url()->previous();
    }
}
