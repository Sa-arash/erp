<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Benefit;
use App\Models\CompanyUser;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Typeleave;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use function Filament\Support\is_app_url;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

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
            $company = $this->record;
            CompanyUser::query()->create([
                'company_id' => $company->id,
                'user_id' => $this->record->user_id,
            ]);

            Employee::query()->create(['daily_salary',
                'contract_id'=>$company->contracts[0]->id,
                'phone_number'=>"",
               'user_id'=>$company->users[0]->id,
                'fullName'=>$company->users[0]->name,
                'email'=>$company->users[0]->email,
                'joining_date'=>now(),
                'department_id'=>$company->departments[0]->id,
                'position_id'=>$company->positions[0]->id,
                'gender'=>'male',
                'company_id'=>$company->id,
                'duty_id'=>$company->duties[0]->id,
                'card_status'=>"National Staff",
                'type_of_ID'=>"Renewal",
                'ID_number'=>"011",
            ]);

            Benefit::query()->create([
                'title' => 'Overtime',
                'built_in' => 1,
                'price_type' => 0,
                'percent' => null,
                'amount' => 0,
                'type' => 'allowance',
                'company_id' => $company->id
            ]);
            Benefit::query()->create([
                'title' => 'Leave',
                'built_in' => 1,
                'price_type' => 0,
                'percent' => null,
                'amount' => 0,
                'type' => 'deduction',
                'company_id' => $company->id
            ]);
            Typeleave::query()->create([
                'title'=>"Annual Leaves",
                'days'=>24,
                'built_in'=>1,
                'description'=>null,
                'company_id'=>$company->id,
                'is_payroll'=>1
            ]);

            $currency=Currency::query()->create([
                'name'=>getCurrency()[$data['currency']],
                'symbol'=>$data['currency'],
                'company_id'=>$this->record->id,
                'is_company_currency'=> 1,
                'exchange_rate'=>1
            ]);
            $accounts = [
                [
                    'id' => 1,
                    'name' => 'Assets',
                    'type' => 'debtor',
                    'code' => '10',
                    'level' => 'main',
                    'group' => 'Asset',
                    'parent_id' => null,
                    'built_in' => true,
                    'description' => 'Group of all asset accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id
                ],
                [
                    'id' => 2,
                    'name' => 'Current Assets',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10001',
                    'level' => 'group',
                    'parent_id' => 1,
                    'built_in' => true,
                    'description' => 'Current asset accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 3,
                    'name' => 'Bank',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10001110',
                    'level' => 'general',
                    'parent_id' => 2,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 4,
                    'name' => 'Cash',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10001120',
                    'level' => 'general',
                    'parent_id' => 2,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 5,
                    'name' => 'Accounts Receivable',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'has_cheque'=>1,
                    'code' => '10001130',
                    'level' => 'general',
                    'parent_id' => 2,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 6,
                    'name' => 'Inventory',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10001140',
                    'level' => 'general',
                    'parent_id' => 2,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 7,
                    'name' => 'Fixed Assets',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10002',
                    'level' => 'group',
                    'parent_id' => 1,
                    'built_in' => true,
                    'description' => 'Fixed asset accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 8,
                    'name' => 'Buildings',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10002110',
                    'level' => 'general',
                    'parent_id' => 7,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 9,
                    'name' => 'Vehicles',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10002120',
                    'level' => 'general',
                    'parent_id' => 7,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 10,
                    'name' => 'Equipment',
                    'type' => 'debtor',
                    'group' => 'Asset',
                    'code' => '10002130',
                    'level' => 'general',
                    'parent_id' => 7,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 11,
                    'name' => 'Liabilities',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20',
                    'level' => 'main',
                    'group' => 'Liabilitie',
                    'parent_id' => null,
                    'built_in' => true,
                    'description' => 'Group of all liability accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 12,
                    'name' => 'Current Liabilities',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20001',
                    'level' => 'group',
                    'parent_id' => 11,
                    'built_in' => true,
                    'description' => 'Current liability accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 13,
                    'name' => 'Accounts Payable',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20001110',
                    'has_cheque'=>1,
                    'level' => 'general',
                    'parent_id' => 12,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 14,
                    'name' => 'Bank Loans',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20001120',
                    'level' => 'general',
                    'parent_id' => 12,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 15,
                    'name' => 'Long-Term Liabilities',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20002',
                    'level' => 'group',
                    'parent_id' => 11,
                    'built_in' => true,
                    'description' => 'Long-term liability accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 16,
                    'name' => 'Mortgages',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20002110',
                    'level' => 'general',
                    'parent_id' => 15,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 17,
                    'name' => 'Bonds Payable',
                    'type' => 'creditor',
                    'group' => 'Liabilitie',
                    'code' => '20002120',
                    'level' => 'general',
                    'parent_id' => 15,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 18,
                    'name' => 'Equity',
                    'type' => 'creditor',
                    'code' => '30',
                    'level' => 'main',
                    'group' => 'Equity',
                    'parent_id' => null,
                    'built_in' => true,
                    'description' => 'Equity accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 19,
                    'name' => 'Retained Earnings',
                    'type' => 'creditor',
                    'code' => '30001',
                    'level' => 'general',
                    'group' => 'Equity',
                    'parent_id' => 18,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 20,
                    'name' => "Owner's Equity",
                    'type' => 'creditor',
                    'code' => '30002',
                    'level' => 'general',
                    'parent_id' => 18,
                    'group' => 'Equity',
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 21,
                    'name' => 'Income',
                    'type' => 'creditor',
                    'code' => '40',
                    'level' => 'main',
                    'parent_id' => null,
                    'group' => 'Income',
                    'built_in' => true,
                    'description' => 'Income accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 22,
                    'name' => 'Sales Revenue',
                    'type' => 'creditor',
                    'code' => '40001',
                    'level' => 'general',
                    'parent_id' => 21,
                    'group' => 'Income',
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 23,
                    'name' => 'Service Revenue',
                    'type' => 'creditor',
                    'code' => '40002',
                    'group' => 'Income',
                    'level' => 'general',
                    'parent_id' => 21,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 24,
                    'name' => 'Interest Income',
                    'type' => 'creditor',
                    'code' => '40003',
                    'level' => 'general',
                    'parent_id' => 21,
                    'group' => 'Income',
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 25,
                    'name' => 'Expenses',
                    'type' => 'debtor',
                    'code' => '50',
                    'level' => 'main',
                    'group' => 'Expense',
                    'parent_id' => null,
                    'built_in' => true,
                    'description' => 'Expense accounts',
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 26,
                    'name' => 'Rent Expense',
                    'type' => 'debtor',
                    'code' => '50001',
                    'level' => 'general',
                    'group' => 'Expense',
                    'parent_id' => 25,
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 27,
                    'name' => 'Utilities',
                    'type' => 'debtor',
                    'code' => '50002',
                    'level' => 'general',
                    'parent_id' => 25,
                    'built_in' => true,
                    'group' => 'Expense',
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
                [
                    'id' => 28,
                    'name' => 'Office Supplies',
                    'type' => 'debtor',
                    'code' => '50003',
                    'level' => 'general',
                    'parent_id' => 25,
                    'group' => 'Expense',
                    'built_in' => true,
                    'description' => null,
                    'company_id' => $company->id,
                    'currency_id'=>$currency->id                ],
            ];

            $tempIds = [];

            foreach ($accounts as $account) {

                $account['stamp']=$account['name'];
                if ($account['id'] == 18) {
                    $account['name'] = "Equity`s";
                } elseif ($account['id'] == 21) {
                    $account['name'] = 'Incomes';
                }
                $originalId = $account['id'];
                unset($account['id']);
                $newId = DB::table('accounts')->insertGetId($account);
                $tempIds[$originalId] = $newId;
            }

            foreach ($accounts as $account) {
                if ($account['parent_id'] !== null) {
                    DB::table('accounts')
                        ->where('id', $tempIds[$account['id']])
                        ->update(['parent_id' => $tempIds[$account['parent_id']]]);
                }
            }

            $superAdminRole=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'SuperAdmin'
            ]);
            $superAdminRole->permissions()->attach(getAllPermission());
            $users= User::query()->where('is_super',1)->get();
            foreach ($users as $user){
                $user->roles()->attach([
                    $superAdminRole->id=>[
                        'company_id'=>$company->id
                    ]
                ]);
            }
            $ceo=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'CEO'
            ]);
            $head=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'Head of Department'
            ]);
            $admin=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'Admin'
            ]);
            $logestic=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'Logestic'
            ]);
            $security=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'Security'
            ]);
            $user=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'User'
            ]);
            $operation=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'Operation'
            ]);
            $reception=Role::query()->create([
                'company_id'=>$company->id,
                'guard_name'=>'web',
                'is_show'=>0,
                'name'=>'Reception'
            ]);


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
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

}
