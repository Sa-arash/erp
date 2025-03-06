<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\CompanyUser;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;



    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');
            if (isset($this->oldFormState['data'])) {
                $userId = $this->oldFormState['data']['user_id'];
                if ($this->form->getState()['user_id'] != $userId) {
                    $pivot=$this->record->users->where('id', $userId    )->first();

                    if ($pivot){
                        $pivot->pivot->delete();
                        CompanyUser::query()->create([
                            'company_id' => $this->record->id,
                            'user_id' => $this->form->getState()['user_id'],
                        ]);

                    }
                }
            }

            $data = $this->form->getState(afterValidate: function () {
                $this->callHook('afterValidate');

                $this->callHook('beforeSave');
            });


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
