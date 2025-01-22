<?php

namespace App\Filament\Admin\Resources\PartiesResource\Pages;

use App\Filament\Admin\Resources\PartiesResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class EditParties extends EditRecord
{
    protected static string $resource = PartiesResource::class;

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
//           if (isset($data['parent_customer']) ){
//
//               $parentAccount=Account::query()->where('id',$data['account_customer'])->where('company_id',getCompany()->id)->first();
//               $account = Account::query()->create([
//                   'name' =>  $data['name'].$data['account_code'],
//                   'type' => 'debtor',
//                   'level' => 'detail',
//                   'parent_id' => $parentAccount->id,
//                   'built_in' => false,
//                   'company_id' => getCompany()->id,
//               ]);
//           }elseif (isset($data['parent_vendor'])){
//               $parentAccount=Account::query()->where('id',$data['account_customer'])->where('company_id',getCompany()->id)->first();
//               $account = Account::query()->create([
//                   'name' =>  $data['name'].$data['account_code'],
//                   'type' => 'creditor',
//                   'level' => 'detail',
//                   'parent_id' => $parentAccount->id,
//                   'built_in' => false,
//                   'company_id' => getCompany()->id,
//               ]);
//           }else{
//               $parentAccount=Account::query()->where('id',$data['parent_vendor'])->where('company_id',getCompany()->id)->first();
//               $account = Account::query()->create([
//                   'name' =>  $data['name'].$data['account_code'],
//                   'type' => 'creditor',
//                   'code' => $parentAccount->code . $data['account_code'],
//                   'level' => 'detail',
//                   'parent_id' => $parentAccount->id,
//                   'built_in' => false,
//                   'company_id' => getCompany()->id,
//               ]);
//            $data['account_vendor'] = $account->id;
//
//               $parentAccount=Account::query()->where('id',$data['parent_customer'])->where('company_id',getCompany()->id)->first();
//               $account = Account::query()->create([
//                   'name' =>  $data['name'].$data['account_code'],
//                   'type' => 'debtor',
//                   'code' => $parentAccount->code . $data['account_code'],
//                   'level' => 'detail',
//                   'parent_id' => $parentAccount->id,
//                   'built_in' => false,
//                   'company_id' => getCompany()->id,
//               ]);
//               $data['account_vendor'] = $account->id;
//
//           }

//            if ($data['type'] === "vendor"){
//                $parentAccount=Account::query()->where('id',$data['account_vendor'])->where('company_id',getCompany()->id)->first();
//
//            }elseif ($data['type'] === "customer"){
//                $parentAccount=Account::query()->where('id',$data['account_customer'])->where('company_id',getCompany()->id)->first();
//                $account = Account::query()->create([
//                    'name' =>  $data['name'].$data['account_code'],
//                    'type' => 'debtor',
//                    'code' => $parentAccount->code . $data['account_code'],
//                    'level' => 'detail',
//                    'parent_id' => $parentAccount->id,
//                    'built_in' => false,
//                    'company_id' => getCompany()->id,
//                ]);
//            }else{
//
//            }
//            $data = $this->mutateFormDataBeforeSave($data);
//            if ($data['type'] === "vendor") {
//                if (getCompany()->vendor_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//
//                }else{
//                    $parent="Current Liabilities";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//                if ($this->record->account_vendor){
//                    $account = Account::query()->where('id',$this->record->account_vendor)->update([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'debtor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                    $account=Account::query()->firstWhere('id',$this->record->account_vendor);
//                }else{
//                    $account = Account::query()->create([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'debtor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                }
//
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
//                if ($this->record->account_customer){
//                    $account = Account::query()->where('id',$this->record->account_customer)->update([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'creditor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//
//                    $account=Account::query()->firstWhere('id',$this->record->account_vendor);
//
//                }else{
//                    $account = Account::query()->create([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'creditor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                }
//                $data['account_customer'] = $account->id;
//
//            }
//            if ($data['type'] === "both"){
//                if (getCompany()->vendor_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//                }else{
//                    $parent="Current Liabilities";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//
//                if ($this->record->account_vendor){
//                    $account = Account::query()->where('id',$this->record->account_vendor)->update([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'debtor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                    $account=Account::query()->firstWhere('id',$this->record->account_vendor);
//                }else{
//                    $account = Account::query()->create([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'debtor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                }
//                $data['account_vendor'] = $account->id;
//                if (getCompany()->customer_account !==null){
//                    $parent=getCompany()->account_bank;
//                    $parentAccount=Account::query()->where('id',$parent)->where('company_id',getCompany()->id)->first();
//                }else{
//                    $parent="Accounts Receivable";
//                    $parentAccount=Account::query()->where('stamp',$parent)->where('company_id',getCompany()->id)->first();
//                }
//
//                if ($this->record->account_customer){
//                    $account = Account::query()->where('id',$this->record->account_customer)->update([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'debtor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                    $account=Account::query()->firstWhere('id',$this->record->account_customer);
//
//                }else{
//                    $account = Account::query()->create([
//                        'name' =>  $data['name'].$data['account_code'],
//                        'type' => 'debtor',
//                        'code' => $parentAccount->code . $data['account_code'],
//                        'level' => 'detail',
//                        'parent_id' => $parentAccount->id,
//                        'built_in' => false,
//                        'company_id' => getCompany()->id,
//                    ]);
//                }
//                $data['account_customer'] = $account->id;
//
//            }

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
