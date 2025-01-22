<?php

namespace App\Filament\Admin\Resources\BankResource\Pages;

use App\Filament\Admin\Resources\BankResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreateBank extends CreateRecord
{
    protected static string $resource = BankResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);
            if (getCompany()->account_bank !==null){
                $parent=getCompany()->account_bank;
                $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();

            }else{
                $parent="Bank";
                $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
            }
            $account = Account::query()->create([
                'name' => $data['bank_name'] ,
                'type' => 'debtor',
                'code' => $parentAccount->code . $data['account_code'],
                'level' => 'detail',
                'parent_id' => $parentAccount->id,
                'built_in' => false,
                'company_id' => getCompany()->id,
            ]);
            $data['account_id'] = $account->id;

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
