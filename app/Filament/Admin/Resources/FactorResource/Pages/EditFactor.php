<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use App\Models\Currency;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class EditFactor extends EditRecord
{
    protected static string $resource = FactorResource::class;


    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();

        foreach ($this->data['invoice']['transactions']  as $key=> $datum){

            if ($datum['cheque']['due_date']){
                $this->data['invoice']['transactions'][$key]['Cheque']=true;
            }
        }

        $this->previousUrl = url()->previous();
    }
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

            $debtor=0;
            $creditor=0;
            foreach ($this->data['invoice']['transactions'] as $transaction){
                if ($transaction['creditor'] >0){
                    $creditor+=str_replace(',','',$transaction['creditor']);
                }else{
                    $debtor+=str_replace(',','',$transaction['debtor']);
                }
            }
            if (round($debtor,2) !== round($creditor,2)){
                Notification::make('warning')->title('Creditor and Debtor not Equal')->warning()->send();
                return;
            }
            $currency = Currency::query()->firstWhere('id',$data['currency_id']);

            $total = 0;
            foreach ($this->form->getLivewire()->data['items'] as $item) {
                $total += str_replace(',', '', $item['total']);
            }
            $totalPure=$total;
            $total=$total*$currency?->exchange_rate;
            if (round($debtor,2) !== round($total,2)){
                Notification::make('warning')->title('Invoice Total and Journal Entry Total not Equal')->warning()->send();
                return;
            }

            $data = $this->mutateFormDataBeforeSave($data);

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



}
