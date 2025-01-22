<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank_category;
use App\Models\Benefit;
use App\Models\BenefitEmployee;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Documentation;
use App\Models\Duty;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\LoanPay;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\Typeleave;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
        ]);
        Company::factory()->create([
            'id' => 1,
            'title' => 'test user fac',
            'user_id' => 1,
            'description' => ' test company ',
        ]);
        CompanyUser::factory()->create([
            'user_id' => 1,
            'company_id' => 1,
        ]);
        Position::factory()->create([
            'title' => 'manager',
            'company_id' => 1,

            'description' => 'this is test position for manager',
        ]);
        Employee::factory()->create([
            'fullName'=>'test employee',
            'user_id' => 1,
            'company_id' => 1,
            'position_id'=>1,
        ]);
        $accounts = [
            [
                'id' => 1,
                'name' => 'Assets',
                'type' => 'debtor',
                'code' => '10',
                'level' => 'main',
                'group'=>'Asset',
                'parent_id' => null,
                'built_in' => true,
                'description' => 'Group of all asset accounts',
                'company_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Current Assets',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10001',
                'level' => 'group',
                'parent_id' => 1,
                'built_in' => true,
                'description' => 'Current asset accounts',
                'company_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Bank',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10001110',
                'level' => 'general',
                'parent_id' => 2,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Cash',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10001120',
                'level' => 'general',
                'parent_id' => 2,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 5,
                'name' => 'Accounts Receivable',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10001130',
                'level' => 'general',
                'parent_id' => 2,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 6,
                'name' => 'Inventory',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10001140',
                'level' => 'general',
                'parent_id' => 2,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 7,
                'name' => 'Fixed Assets',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10002',
                'level' => 'group',
                'parent_id' => 1,
                'built_in' => true,
                'description' => 'Fixed asset accounts',
                'company_id' => 1,
            ],
            [
                'id' => 8,
                'name' => 'Buildings',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10002110',
                'level' => 'general',
                'parent_id' => 7,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 9,
                'name' => 'Vehicles',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10002120',
                'level' => 'general',
                'parent_id' => 7,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 10,
                'name' => 'Equipment',
                'type' => 'debtor',
                'group'=>'Asset',
                'code' => '10002130',
                'level' => 'general',
                'parent_id' => 7,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 11,
                'name' => 'Liabilities',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20',
                'level' => 'main',
                'group'=>'Liabilitie',
                'parent_id' => null,
                'built_in' => true,
                'description' => 'Group of all liability accounts',
                'company_id' => 1,
            ],
            [
                'id' => 12,
                'name' => 'Current Liabilities',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20001',
                'level' => 'group',
                'parent_id' => 11,
                'built_in' => true,
                'description' => 'Current liability accounts',
                'company_id' => 1,
            ],
            [
                'id' => 13,
                'name' => 'Accounts Payable',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20001110',
                'level' => 'general',
                'parent_id' => 12,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 14,
                'name' => 'Bank Loans',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20001120',
                'level' => 'general',
                'parent_id' => 12,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 15,
                'name' => 'Long-Term Liabilities',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20002',
                'level' => 'group',
                'parent_id' => 11,
                'built_in' => true,
                'description' => 'Long-term liability accounts',
                'company_id' => 1,
            ],
            [
                'id' => 16,
                'name' => 'Mortgages',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20002110',
                'level' => 'general',
                'parent_id' => 15,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 17,
                'name' => 'Bonds Payable',
                'type' => 'creditor',
                  'group'=>'Liabilitie',
                'code' => '20002120',
                'level' => 'general',
                'parent_id' => 15,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 18,
                'name' => 'Equity',
                'type' => 'creditor',
                'code' => '30',
                'level' => 'main',
                'group'=>'Equity',
                'parent_id' => null,
                'built_in' => true,
                'description' => 'Equity accounts',
                'company_id' => 1,
            ],
            [
                'id' => 19,
                'name' => 'Retained Earnings',
                'type' => 'creditor',
                'code' => '30001',
                'level' => 'general',
                'group'=>'Equity',
                'parent_id' => 18,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 20,
                'name' => "Owner's Equity",
                'type' => 'creditor',
                'code' => '30002',
                'level' => 'general',
                'parent_id' => 18,
                'group'=>'Equity',
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 21,
                'name' => 'Income',
                'type' => 'creditor',
                'code' => '40',
                'level' => 'main',
                'parent_id' => null,
                'group'=>'Income',
                'built_in' => true,
                'description' => 'Income accounts',
                'company_id' => 1,
            ],
            [
                'id' => 22,
                'name' => 'Sales Revenue',
                'type' => 'creditor',
                'code' => '40001',
                'level' => 'general',
                'parent_id' => 21,
                'group'=>'Income',
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 23,
                'name' => 'Service Revenue',
                'type' => 'creditor',
                'code' => '40002',
                 'group'=>'Income',
                'level' => 'general',
                'parent_id' => 21,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 24,
                'name' => 'Interest Income',
                'type' => 'creditor',
                'code' => '40003',
                'level' => 'general',
                'parent_id' => 21,
                 'group'=>'Income',
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 25,
                'name' => 'Expenses',
                'type' => 'debtor',
                'code' => '50',
                'level' => 'main',
                'group'=>'Expense',
                'parent_id' => null,
                'built_in' => true,
                'description' => 'Expense accounts',
                'company_id' => 1,
            ],
            [
                'id' => 26,
                'name' => 'Rent Expense',
                'type' => 'debtor',
                'code' => '50001',
                'level' => 'general',
                'group'=>'Expense',
                'parent_id' => 25,
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 27,
                'name' => 'Utilities',
                'type' => 'debtor',
                'code' => '50002',
                'level' => 'general',
                'parent_id' => 25,
                'built_in' => true,
                'group'=>'Expense',
                'description' => null,
                'company_id' => 1,
            ],
            [
                'id' => 28,
                'name' => 'Office Supplies',
                'type' => 'debtor',
                'code' => '50003',
                'level' => 'general',
                'parent_id' => 25,
                'group'=>'Expense',
                'built_in' => true,
                'description' => null,
                'company_id' => 1,
            ],
        ];

        foreach ($accounts as $account) {
            $account['stamp']=$account['name'];
            DB::table('accounts')->insert($account);
        }


        
        //     Contract::factory(10)->create();
        //     User::factory(10)->create();
        //     Company::factory(10)->create();
        //     Department::factory(5)->create();
        //     Position::factory(5)->create();
        //     Duty::factory(10)->create();
        //     Benefit::factory(10)->create();
        //     Documentation::factory(10)->create();
        //     Employee::factory(10)->create();
        //     Typeleave::factory(10)->create();
        //     Leave::factory(10)->create();
        //     Loan::factory(10)->create();
        //     Payroll::factory(10)->create();
        //     LoanPay::factory(10)->create();
        //     CompanyUser::factory(10)->create();
        //     BenefitEmployee::factory(10)->create();

        //     Bank_category::factory(10)->create();
        //     Account::factory(10)->create();
        //     Customer::factory(10)->create();
        //     Vendor::factory(10)->create();
        //     Income::factory(10)->create();
        //     Expense::factory(10)->create();

        // $company = Company::find(1);

        // if ($company) {
        //     $accounts = Account::factory()->count(10)->make();
        //     $company->accounts()->saveMany($accounts);
        // }
        //         $customers = Customer::factory()->count(10)->make();
        //         $company->customers()->saveMany($customers);
        //         $vendors = Vendor::factory()->count(10)->make();
        //         $company->vendors()->saveMany($vendors);
        //         $bankCategories = Bank_category::factory()->count(10)->make();
        //         $company->bankCategories()->saveMany($bankCategories);
        //         $incomes = Income::factory()->count(10)->make();
        //         $company->incomes()->saveMany($incomes);
        //         $expenses = Expense::factory()->count(10)->make();
        //         $company->expenses()->saveMany($expenses);





        //         $contracts = Contract::factory()->count(10)->make();
        //         $company->contracts()->saveMany($contracts);



        //         $departments = Department::factory()->count(5)->make();
        //         $company->departments()->saveMany($departments);

        //         $positions = Position::factory()->count(5)->make();
        //         $company->positions()->saveMany($positions);

        //         $duties = Duty::factory()->count(10)->make();
        //         $company->duties()->saveMany($duties);

        //         $benefits = Benefit::factory()->count(10)->make();
        //         $company->benefits()->saveMany($benefits);


        //         $employees = Employee::factory()->count(10)->make();
        //         $company->employees()->saveMany($employees);

        //         $typeLeaves = Typeleave::factory()->count(10)->make();
        //         $company->typeLeaves()->saveMany($typeLeaves);

        //         $leaves = Leave::factory()->count(10)->make();
        //         $company->leaves()->saveMany($leaves);

        //         $loans = Loan::factory()->count(10)->make();
        //         $company->loans()->saveMany($loans);

        //         $payrolls = Payroll::factory()->count(10)->make();
        //         $company->payrolls()->saveMany($payrolls);


        //         $bankCategories = Bank_category::factory()->count(10)->make();
        //         $company->bankCategories()->saveMany($bankCategories);

        //         $accounts = Account::factory()->count(10)->make();
        //         $company->accounts()->saveMany($accounts);

        //         $customers = Customer::factory()->count(10)->make();
        //         $company->customers()->saveMany($customers);

        //         $vendors = Vendor::factory()->count(10)->make();
        //         $company->vendors()->saveMany($vendors);



        //         $incomes = Income::factory()->count(10)->make();
        //         $company->incomes()->saveMany($incomes);

        //         $expenses = Expense::factory()->count(10)->make();
        //         $company->expenses()->saveMany($expenses);
        //     }
    }
}

// $loanPays = LoanPay::factory()->count(10)->make();
// $company->loanPays()->saveMany($loanPays);

// $companyUsers = CompanyUser::factory()->count(10)->make();
// $company->companyUsers()->saveMany($companyUsers);

// $benefitEmployees = BenefitEmployee::factory()->count(10)->make();
// $company->benefitEmployees()->saveMany($benefitEmployees);
