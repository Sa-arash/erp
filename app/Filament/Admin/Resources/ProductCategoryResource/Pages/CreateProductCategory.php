<?php

namespace App\Filament\Admin\Resources\ProductCategoryResource\Pages;

use App\Filament\Admin\Resources\ProductCategoryResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;

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
            if (getCompany()->category_account){
                $account = getCompany()->accounts()->where('id', getCompany()->category_account )->first();
            }else{
                $account = getCompany()->accounts()->where('stamp', 'Fixed Assets')->first();
            }

            $categoryAccount = Account::query()->create([
                'name' => $this->data['title'],
                'type' => 'debtor',
                'stamp' => $this->data['title'],
                'code' => $account->childerns->last()?->code !== null ?  generateNextCodeDote($account->childerns->last()?->code):$account?->code."001",
                'level' => 'subsidiary',
                'parent_id' => $account?->id,
                'company_id' => getCompany()->id,
            ]);
            $data['account_id'] = $categoryAccount->id;

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
