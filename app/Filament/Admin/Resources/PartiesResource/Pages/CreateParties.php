<?php

namespace App\Filament\Admin\Resources\PartiesResource\Pages;

use App\Filament\Admin\Resources\PartiesResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreateParties extends CreateRecord
{
    protected static string $resource = PartiesResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            // dd($data);
            if ($data['type'] === "vendor"){
                $parentAccount=Account::query()->where('id',$data['parent_vendor'])->where('company_id',getCompany()->id)->first();
                $account = Account::query()->create([
                    'currency_id' =>  $data['currency_id'],
                    'name' =>  $data['name'],
                    'type' => 'creditor',
                    'code' => $parentAccount->code . $data['account_code_vendor'],
                    'level' => 'detail',
                    'parent_id' => $parentAccount->id,
                    'built_in' => false,
                    'company_id' => getCompany()->id,
                ]);
                $data['account_vendor']=$account->id;
            }elseif ($data['type'] === "customer"){
                $parentAccount=Account::query()->where('id',$data['parent_customer'])->where('company_id',getCompany()->id)->first();
                $account = Account::query()->create([
                    'name' =>  $data['name'],
                    'type' => 'debtor',
                    'currency_id' =>  $data['currency_id'],
                    'code' => $parentAccount->code . $data['account_code_customer'],
                    'level' => 'detail',
                    'parent_id' => $parentAccount->id,
                    'built_in' => false,
                    'company_id' => getCompany()->id,
                ]);
                $data['account_customer']=$account->id;

            }else{
                $parentAccount=Account::query()->where('id',$data['parent_vendor'])->where('company_id',getCompany()->id)->first();

                $account = Account::query()->create([
                    'name' =>  $data['name'],
                    'currency_id' =>  $data['currency_id'],
                    'type' => 'creditor',
                    'code' => $parentAccount->code . $data['account_code_vendor'],
                    'level' => 'detail',
                    'parent_id' => $parentAccount->id,
                    'built_in' => false,
                    'company_id' => getCompany()->id,
                    'Group'=>'Liabilitie'
                ]);
                $data['account_vendor']=$account->id;

                $parentAccount=Account::query()->where('id',$data['parent_customer'])->where('company_id',getCompany()->id)->first();
                $account = Account::query()->create([
                    'name' =>  $data['name'],
                    'currency_id' =>  $data['currency_id'],
                    'type' => 'debtor',
                    'code' => $parentAccount->code . $data['account_code_customer'],
                    'level' => 'detail',
                    'parent_id' => $parentAccount->id,
                    'built_in' => false,
                    'company_id' => getCompany()->id,
                    'Group'=>'Asset'
                ]);
                $data['account_customer']=$account->id;

            }


//            if ($data['type'] === "vendor") {
//                if (getCompany()->vendor_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//
//                }else{
//                    $parent="Current Current Liabilities";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//            $account = Account::query()->create([
//                    'name' =>  $data['name'].$data['account_code'],
//                    'type' => 'debtor',
//                    'code' => $parentAccount->code . $data['account_code'],
//                    'level' => 'detail',
//                    'parent_id' => $parentAccount->id,
//                    'built_in' => false,
//                    'company_id' => getCompany()->id,
//                ]);
//                $data['account_vendor'] = $account->id;
//
//            }
//            if ($data['type'] === "customer"){
//                if (getCompany()->customer_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//
//                }else{
//                    $parent="Accounts Receivable";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//                $account = Account::query()->create([
//                    'name' =>  $data['name'].$data['account_code'],
//                    'type' => 'creditor',
//                    'code' => $parentAccount->code . $data['account_code'],
//                    'level' => 'detail',
//                    'parent_id' => $parentAccount->id,
//                    'built_in' => false,
//                    'company_id' => getCompany()->id,
//                ]);
//                $data['account_customer'] = $account->id;
//
//            }
//            if ($data['type'] === "both"){
//                if (getCompany()->vendor_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//
//                }else{
//
//                    $parent="Current Liabilities";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//                $account = Account::query()->create([
//                    'name' =>  $data['name'].$data['account_code'],
//                    'type' => 'debtor',
//                    'code' => $parentAccount->code . $data['account_code'],
//                    'level' => 'detail',
//                    'parent_id' => $parentAccount->id,
//                    'built_in' => false,
//                    'company_id' => getCompany()->id,
//                ]);
//                $data['account_vendor'] = $account->id;
//
//                if (getCompany()->customer_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//
//                }else{
//                    $parent="Accounts Receivable";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//                $account = Account::query()->create([
//                    'name' =>  $data['name'].$data['account_code'],
//                    'type' => 'creditor',
//                    'code' => $parentAccount->code . $data['account_code'],
//                    'level' => 'detail',
//                    'parent_id' => $parentAccount->id,
//                    'built_in' => false,
//                    'company_id' => getCompany()->id,
//                ]);
//                $data['account_customer'] = $account->id;
//            }

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
