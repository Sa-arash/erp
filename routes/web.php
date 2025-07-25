<?php

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Approval;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\AssetEmployeeItem;
use App\Models\Bank;
use App\Models\Benefit;
use App\Models\BenefitEmployee;
use App\Models\BenefitPayroll;
use App\Models\Bid;
use App\Models\Brand;
use App\Models\Cheque;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Documentation;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\FinancialPeriod;
use App\Models\Holiday;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\LoanPay;
use App\Models\Overtime;
use App\Models\Package;
use App\Models\Parties;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Separation;
use App\Models\Service;
use App\Models\Stock;
use App\Models\Structure;
use App\Models\TakeOut;
use App\Models\TakeOutItem;
use App\Models\Task;
use App\Models\TaskEmployee;
use App\Models\TaskReports;
use App\Models\Transaction;
use App\Models\Typeleave;
use App\Models\Unit;
use App\Models\UrgentLeave;
use App\Models\User;
use App\Models\VisitorRequest;
use App\Models\Warehouse;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\LoginResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


Route::get('/testLog', function () {
    dd(
    Account::query()->first()?->log,
    AccountType::query()->first()?->log,
    Approval::query()->first()->log,
    Asset::query()->first()?->log,
    AssetEmployee::query()->first()?->log,
    AssetEmployeeItem::query()->first()?->log,
    // Bank_category::query()->first()?->log,
    Bank::query()->first()?->log,
    Benefit::query()->first()?->log,
    BenefitEmployee::query()->first()?->log,
    BenefitPayroll::query()->first()?->log,
    Bid::query()->first()?->log,
    Brand::query()->first()?->log,
    Cheque::query()->first()?->log,
    Company::query()->first()?->log,
    CompanyUser::query()->first()?->log,
    Contract::query()->first()?->log,
    Currency::query()->first()?->log,
    Customer::query()->first()?->log,
    Department::query()->first()?->log,
    Documentation::query()->first()?->log,
    Employee::query()->first()?->log,
    // Expense::query()->first()?->log,
    Factor::query()->first()?->log,
    FactorItem::query()->first()?->log,
    FinancialPeriod::query()->first()?->log,
    Holiday::query()->first()?->log,
    // Income::query()->first()?->log,
    Inventory::query()->first()?->log,
    Invoice::query()->first()?->log,
    Leave::query()->first()?->log,
    Loan::query()->first()?->log,
    LoanPay::query()->first()?->log,
    Overtime::query()->first()?->log,
    Package::query()->first()?->log,
    Parties::query()->first()?->log,
    Payroll::query()->first()?->log,
    Position::query()->first()?->log,
    Product::query()->first()?->log,
    // ProductCategory::query()->first()?->log,
    // ProductSubCategory::query()->first()?->log,
    Project::query()->first()?->log,
    PurchaseOrder::query()->first()?->log,
    PurchaseOrderItem::query()->first()?->log,
    PurchaseRequest::query()->first()?->log,
    PurchaseRequestItem::query()->first()?->log,
    Quotation::query()->first()?->log,
    QuotationItem::query()->first()?->log,
    Separation::query()->first()?->log,
    Service::query()->first()?->log,
    Stock::query()->first()?->log,
    Structure::query()->first()?->log,
    TakeOut::query()->first()?->log,
    TakeOutItem::query()->first()?->log,
    Task::query()->first()?->log,
    TaskEmployee::query()->first()?->log,
    TaskReports::query()->first()?->log,
    Transaction::query()->first()?->log,
    Typeleave::query()->first()?->log,
    Unit::query()->first()?->log,
    UrgentLeave::query()->first()?->log,
    User::query()->first()?->log,
    VisitorRequest::query()->first()?->log,
    Warehouse::query()->first()?->log,






   );
});


Route::get('/', function () {


    return view('welcome');
});
Route::get('/test', function () {


//    $sessions = \Illuminate\Support\Facades\Redis::connection('cache');
//
//    foreach ($sessions as $key) {
//        $sessionData = \Illuminate\Support\Facades\Redis::get($key);
//        dump([
//            'key' => $key,
//            'data' => unserialize(json_decode($sessionData))
//        ]);
//    }
//    dd($sessions);
//    $auth = auth()->user();
//
//
//    dd($count);
//    $t=TakeOut::query()->where('id',8)->first();
//    $data=[];
//    foreach ($t->itemsOut as $item){
//        $item['unit']='EA';
//        $data[]=$item;
//    }
//    $t->update(['itemsOut'=>$data]);
//    $roll=\Spatie\Permission\Models\Role::query()->firstWhere('id',1);
//    dd($roll->permissions->pluck('id')->toArray());
//    $vi=VisitorRequest::query()->get();
//    foreach ( $vi as $item){
//        $datas=[];
//        foreach ($item->visiting_dates as $date){
//
//            $datas[]=Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y');
//        }
//        $item->update(['visiting_dates'=>$datas]);
//    }
//    dd($vi);
//    $approves=Approval::query()->get();
//    foreach ($approves as $approve){
//        if (empty($approve->approvable)){
//            $approve->delete();
//        }
//    }
//    dd(1);

//    $accounts = Account::query()->get();
//    foreach ($accounts as $account) {
//        if ($account->group === "Asset") {
//            $account->update(['type' => 'debtor']);
//        } elseif ($account->group === "Liabilitie") {
//            $account->update(['type' => 'creditor']);
//        } elseif ($account->group === 'Equity') {
//            $account->update(['type' => 'creditor']);
//        } elseif ($account->group === 'Income') {
//            $account->update(['type' => 'creditor']);
//        } elseif ($account->group === 'Expense') {
//            $account->update(['type' => 'creditor']);
//        }
//    }
//    dd(1);
});


Route::get('artisan',function (){
    \Illuminate\Support\Facades\Artisan::call('queue:work');
})->name('artisan');

Route::get('superAdmin', function () {

    if (Session::get('super') !== null){

        $session=Session::get('company')[0];

        Filament::auth()->loginUsingId(2, true);
        session()->regenerate();

        return redirect()->intended(Filament::getUrl());
    }else{
        dd(1);
        return  redirect('/');
    }
})->name('super.admin.login');

Route::get('login',function (){
  if (Session::get('company') !== null){

      $session=Session::get('company')[0];
      Filament::auth()->logout();
      Filament::auth()->loginUsingId($session, true);
      session()->regenerate();
      app(LoginResponse::class);
      return redirect('super-admin/');
  }else{

      return  redirect('/');
  }


})->name('login');

Route::get('/',function (){

    if (auth()->user() and auth()->user()->is_super){
        return redirect('/super-admin');

    }
    return redirect('/admin');
});
Route::middleware('auth')->group(function(){

Route::get('/pdf/asset/{id}',[\App\Http\Controllers\PdfController::class,'asset'])->name('pdf.asset');
Route::get('/pdf/cashPayment/{id}',[\App\Http\Controllers\PdfController::class,'cashPayment'])->name('pdf.cashPayment');
Route::get('/pdf/clearance/{id}/{company}',[\App\Http\Controllers\PdfController::class,'clearance'])->name('pdf.clearance');
Route::get('/pdf/overtime/{id}',[\App\Http\Controllers\PdfController::class,'overtime'])->name('pdf.overtime');
Route::get('/pdf/leaverequest/{id}',[\App\Http\Controllers\PdfController::class,'leaverequest'])->name('pdf.leaverequest');
Route::get('/pdf/urgentleave/{id}',[\App\Http\Controllers\PdfController::class,'urgentleave'])->name('pdf.urgentleave');
Route::get('/pdf/payroll/{id}/{title}',[\App\Http\Controllers\PdfController::class,'payroll'])->name('pdf.payroll');
Route::get('/pdf/jornal/{transactions}',[\App\Http\Controllers\PdfController::class,'jornal'])->name('pdf.jornal');
Route::get('/pdf/account/{period}/{account}',[\App\Http\Controllers\PdfController::class,'account'])->name('pdf.account');
Route::get('/pdf/balance/{period}',[\App\Http\Controllers\PdfController::class,'balance'])->name('pdf.balance');
Route::get('/pdf/PL/{period}/',[\App\Http\Controllers\PdfController::class,'PL'])->name('pdf.PL');
Route::get('/pdf/document/{document}',[\App\Http\Controllers\PdfController::class,'document'])->name('pdf.document');
Route::get('/pdf/trialBalance/{period}',[\App\Http\Controllers\PdfController::class,'trialBalance'])->name('pdf.trialBalance');
Route::get('/pdf/employee/{id}',[\App\Http\Controllers\PdfController::class,'employee'])->name('pdf.employee');
Route::get('/pdf/payrolls/{ids}',[\App\Http\Controllers\PdfController::class,'payrolls'])->name('pdf.payrolls');
Route::get('/pdf/purchase/{id}',[\App\Http\Controllers\PdfController::class,'purchase'])->name('pdf.purchase');
Route::get('/pdf/po/{id}',[\App\Http\Controllers\PdfController::class,'purchaseOrder'])->name('pdf.po');
Route::get('/pdf/assets/{ids}/{company}/{type}',[\App\Http\Controllers\PdfController::class,'assets'])->name('pdf.assets');
Route::get('/pdf/tasks/{ids}',[\App\Http\Controllers\PdfController::class,'tasks'])->name('pdf.tasks');
Route::get('/pdf/quotation/{id}',[\App\Http\Controllers\PdfController::class,'quotation'])->name('pdf.quotation');
Route::get('/pdf/bid/{id}',[\App\Http\Controllers\PdfController::class,'bid'])->name('pdf.bid');
Route::get('/pdf/separation/{id}',[\App\Http\Controllers\PdfController::class,'separation'])->name('pdf.separation');
Route::get('/pdf/takeOut/{id}',[\App\Http\Controllers\PdfController::class,'takeOut'])->name('pdf.takeOut');
Route::get('/pdf/requestVisit/{id}',[\App\Http\Controllers\PdfController::class,'requestVisit'])->name('pdf.requestVisit');
Route::get('/pdf/entry_and_exit/{id}',[\App\Http\Controllers\PdfController::class,'entryAndExit'])->name('pdf.entryAndExit');
Route::get('/pdf/requestVisit/{ids}/all/{type}',[\App\Http\Controllers\PdfController::class,'requestVisits'])->name('pdf.requestVisits');
Route::get('/pdf/accountCurrency/{period}/{account}',[\App\Http\Controllers\PdfController::class,'accountCurrency'])->name('pdf.accountCurrency');
Route::get('/pdf/barcode/{code}',[\App\Http\Controllers\PdfController::class,'barcode'])->name('pdf.barcode');
Route::get('/pdf/barcodes/{codes}',[\App\Http\Controllers\PdfController::class,'barcodes'])->name('pdf.barcodes');
Route::get('/pdf/qrcodeView/{code}',[\App\Http\Controllers\PdfController::class,'qrcodeView'])->name('pdf.qrcode.view');
Route::get('/pdf/qrcode/{code}',[\App\Http\Controllers\PdfController::class,'qrcode'])->name('pdf.qrcode');
Route::get('/pdf/loan/{id}',[\App\Http\Controllers\PdfController::class,'loan'])->name('pdf.loan');
Route::get('/pdf/cashAdvance/{id}',[\App\Http\Controllers\PdfController::class,'cashAdvance'])->name('pdf.cashAdvance');
Route::get('/pdf/sales/{id}',[\App\Http\Controllers\PdfController::class,'sales'])->name('pdf.sales');
Route::get('/pdf/personals/{id}',[\App\Http\Controllers\PdfController::class,'personals'])->name('pdf.personals');
Route::get('/pdf/assets-balance/{ids}/{company}',[\App\Http\Controllers\PdfController::class,'assetsBalance'])->name('pdf.assets-balance');
Route::get('/pdf/audit/{ids}/{company}',[\App\Http\Controllers\PdfController::class,'audit'])->name('pdf.audit');
Route::get('/pdf/audit-checklist/{company}/{type}',[\App\Http\Controllers\PdfController::class,'auditChecklist'])->name('pdf.audit-checklist');
Route::get('/pdf/employeeAssetHistory/{id}/{type}/{company}',[\App\Http\Controllers\PdfController::class,'employeeAssetHistory'])->name('pdf.employeeAssetHistory');
Route::get('/pdf/employeeAsset/{id}/{type}/{company}',[\App\Http\Controllers\PdfController::class,'employeeAsset'])->name('pdf.employeeAsset');
Route::get('/pdf/grn/{id}/{company}',[\App\Http\Controllers\PdfController::class,'grn'])->name('pdf.grn');

});
Route::get('fix',function (){




    $response = \Illuminate\Support\Facades\Http::get('https://sarafi.af/en/exchange-rates/sarai-shahzada');

    $html = $response->body();

// حذف تگ‌های خراب احتمالی برای جلوگیری از ارور در DOMDocument
    libxml_use_internal_errors(true);

    $doc = new \DOMDocument();
    $doc->loadHTML($html);
    $xpath = new \DOMXPath($doc);

// پیدا کردن ردیف‌هایی که مربوط به "دالر" هستند
    $rows = $xpath->query('//table//tr');

    $usdRate = [];

    foreach ($rows as $row) {
        if (str_contains($row->textContent, 'USD - US Dollar') or str_contains($row->textContent, 'GBP - British Pound')or str_contains($row->textContent, 'EUR - Euro')or str_contains($row->textContent, 'PKR - Pakistani Rupee 1K') or str_contains($row->textContent, 'JPY - Japanese Yen 1K') or str_contains($row->textContent, 'INR - Indian Rupee 1K')or str_contains($row->textContent, 'IRR - Iranian Rial 1K') ) {
            $cols = $row->getElementsByTagName('td');
            $usdRate[trim($cols[0]->textContent)] = [
                'buy' => trim($cols[1]->textContent),
                'sell' => trim($cols[2]->textContent),
            ];
            if (count($usdRate) ==9){
                break;
            }
        }
    }


});



Route::get('account',function (){
    $accounts = [
        [
            'id' => 1,
            'name' => 'Assets',
            'type' => 'debtor',
            'code' => '100',
            'level' => 'group',
            'parent_id' => null,
            'built_in' => true,
            'description' => 'Group of all asset accounts',
            'company_id' => 1,
        ],
        [
            'id' => 2,
            'name' => 'Cash',
            'type' => 'debtor',
            'code' => '100.01',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Cash accounts',
            'company_id' => 1,
        ],
        [
            'id' => 3,
            'name' => 'Petty Cash Funds',
            'type' => 'debtor',
            'code' => '100.01.01',
            'level' => 'subsidiary',
            'parent_id' => 2,
            'built_in' => true,
            'description' => 'Petty cash funds in different currencies',
            'company_id' => 1,
        ],
        [
            'id' => 4,
            'name' => 'Petty Cash Fund- AFN',
            'type' => 'debtor',
            'code' => '100.01.01.0001',
            'level' => 'detail',
            'parent_id' => 3,
            'built_in' => true,
            'description' => 'Petty cash fund in AFN',
            'company_id' => 1,
        ],
        [
            'id' => 5,
            'name' => 'Petty Cash Fund- USD',
            'type' => 'debtor',
            'code' => '100.01.01.0002',
            'level' => 'detail',
            'parent_id' => 3,
            'built_in' => true,
            'description' => 'Petty cash fund in USD',
            'company_id' => 1,
        ],
        [
            'id' => 6,
            'name' => 'Petty Cash Fund- Salaries(AFN & USD)',
            'type' => 'debtor',
            'code' => '100.01.01.0003',
            'level' => 'detail',
            'parent_id' => 3,
            'built_in' => true,
            'description' => 'Petty cash fund for salaries in AFN and USD',
            'company_id' => 1,
        ],
        [
            'id' => 7,
            'name' => 'Petty Cash Fund- Admin(AFN & USD)',
            'type' => 'debtor',
            'code' => '100.01.01.0004',
            'level' => 'detail',
            'parent_id' => 3,
            'built_in' => true,
            'description' => 'Petty cash fund for admin expenses in AFN and USD',
            'company_id' => 1,
        ],
        [
            'id' => 8,
            'name' => 'Deyar Meal Card Cash Collections',
            'type' => 'debtor',
            'code' => '100.01.01.0005',
            'level' => 'detail',
            'parent_id' => 3,
            'built_in' => true,
            'description' => 'Cash collections from Deyar meal cards',
            'company_id' => 1,
        ],
        [
            'id' => 9,
            'name' => 'Petty Cash Fund- Project(AFN & USD)',
            'type' => 'debtor',
            'code' => '100.01.01.0006',
            'level' => 'detail',
            'parent_id' => 3,
            'built_in' => true,
            'description' => 'Petty cash fund for project expenses in AFN and USD',
            'company_id' => 1,
        ],
        [
            'id' => 10,
            'name' => 'Exchange Office Accounts',
            'type' => 'debtor',
            'code' => '100.02',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Exchange office accounts',
            'company_id' => 1,
        ],
        [
            'id' => 11,
            'name' => 'Exchange Office USD',
            'type' => 'debtor',
            'code' => '100.02.01',
            'level' => 'subsidiary',
            'parent_id' => 10,
            'built_in' => true,
            'description' => 'Exchange office accounts in USD',
            'company_id' => 1,
        ],
        [
            'id' => 12,
            'name' => 'Mujtaba',
            'type' => 'debtor',
            'code' => '100.02.01.0001',
            'level' => 'detail',
            'parent_id' => 11,
            'built_in' => true,
            'description' => 'Mujtaba exchange office account',
            'company_id' => 1,
        ],
        [
            'id' => 13,
            'name' => 'Gawhari',
            'type' => 'debtor',
            'code' => '100.02.01.0002',
            'level' => 'detail',
            'parent_id' => 11,
            'built_in' => true,
            'description' => 'Gawhari exchange office account',
            'company_id' => 1,
        ],
        [
            'id' => 14,
            'name' => 'Saraf Accounts',
            'type' => 'debtor',
            'code' => '101',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Saraf accounts',
            'company_id' => 1,
        ],
        [
            'id' => 15,
            'name' => 'Saraf Accounts- KBL',
            'type' => 'debtor',
            'code' => '101.01',
            'level' => 'general',
            'parent_id' => 14,
            'built_in' => true,
            'description' => 'Saraf accounts in Kabul',
            'company_id' => 1,
        ],
        [
            'id' => 16,
            'name' => 'Saraf Account- USD',
            'type' => 'debtor',
            'code' => '101.01.01.0001',
            'level' => 'detail',
            'parent_id' => 15,
            'built_in' => true,
            'description' => 'Saraf account in USD',
            'company_id' => 1,
        ],
        [
            'id' => 17,
            'name' => 'Saraf Account- AFN',
            'type' => 'debtor',
            'code' => '101.01.01.0002',
            'level' => 'detail',
            'parent_id' => 15,
            'built_in' => true,
            'description' => 'Saraf account in AFN',
            'company_id' => 1,
        ],
        [
            'id' => 18,
            'name' => 'Mujtaba',
            'type' => 'debtor',
            'code' => '101.01.01.0003',
            'level' => 'detail',
            'parent_id' => 15,
            'built_in' => true,
            'description' => 'Mujtaba Saraf account',
            'company_id' => 1,
        ],
        [
            'id' => 19,
            'name' => 'Accounts Receivable Customers',
            'type' => 'debtor',
            'code' => '102',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Accounts receivable from customers',
            'company_id' => 1,
        ],
        [
            'id' => 20,
            'name' => 'UN Customers',
            'type' => 'debtor',
            'code' => '102.01',
            'level' => 'general',
            'parent_id' => 19,
            'built_in' => true,
            'description' => 'Accounts receivable from UN customers',
            'company_id' => 1,
        ],
        [
            'id' => 21,
            'name' => 'UNHCR',
            'type' => 'debtor',
            'code' => '102.01.01',
            'level' => 'subsidiary',
            'parent_id' => 20,
            'built_in' => true,
            'description' => 'Accounts receivable from UNHCR',
            'company_id' => 1,
        ],
        [
            'id' => 22,
            'name' => 'UNFAO',
            'type' => 'debtor',
            'code' => '102.01.01.0001',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNFAO',
            'company_id' => 1,
        ],
        [
            'id' => 23,
            'name' => 'UNWHO',
            'type' => 'debtor',
            'code' => '102.01.01.0002',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNWHO',
            'company_id' => 1,
        ],
        [
            'id' => 24,
            'name' => 'UNHABITAT',
            'type' => 'debtor',
            'code' => '102.01.01.0004',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNHABITAT',
            'company_id' => 1,
        ],
        [
            'id' => 25,
            'name' => 'UNESCO',
            'type' => 'debtor',
            'code' => '102.01.01.0005',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNESCO',
            'company_id' => 1,
        ],
        [
            'id' => 26,
            'name' => 'UNIDO',
            'type' => 'debtor',
            'code' => '102.01.01.0006',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNIDO',
            'company_id' => 1,
        ],
        [
            'id' => 27,
            'name' => 'UNWOMEN',
            'type' => 'debtor',
            'code' => '102.01.01.0007',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNWOMEN',
            'company_id' => 1,
        ],
        [
            'id' => 28,
            'name' => 'UNWFP',
            'type' => 'debtor',
            'code' => '102.01.01.0008',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNWFP',
            'company_id' => 1,
        ],
        [
            'id' => 29,
            'name' => 'UNMAS',
            'type' => 'debtor',
            'code' => '102.01.01.0009',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNMAS',
            'company_id' => 1,
        ],
        [
            'id' => 30,
            'name' => 'UNILO',
            'type' => 'debtor',
            'code' => '102.01.01.0010',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UNILO',
            'company_id' => 1,
        ],
        [
            'id' => 31,
            'name' => 'UN-IOM',
            'type' => 'debtor',
            'code' => '102.01.01.0011',
            'level' => 'detail',
            'parent_id' => 21,
            'built_in' => true,
            'description' => 'Accounts receivable from UN-IOM',
            'company_id' => 1,
        ],
        [
            'id' => 32,
            'name' => 'Other Customers',
            'type' => 'debtor',
            'code' => '102.02',
            'level' => 'general',
            'parent_id' => 19,
            'built_in' => true,
            'description' => 'Accounts receivable from other customers',
            'company_id' => 1,
        ],
        [
            'id' => 33,
            'name' => 'AFMAT',
            'type' => 'debtor',
            'code' => '102.02.01.0001',
            'level' => 'detail',
            'parent_id' => 32,
            'built_in' => true,
            'description' => 'Accounts receivable from AFMAT',
            'company_id' => 1,
        ],
        [
            'id' => 34,
            'name' => 'JMS',
            'type' => 'debtor',
            'code' => '102.02.01.0002',
            'level' => 'detail',
            'parent_id' => 32,
            'built_in' => true,
            'description' => 'Accounts receivable from JMS',
            'company_id' => 1,
        ],
        [
            'id' => 35,
            'name' => 'HART SECURITY',
            'type' => 'debtor',
            'code' => '102.02.01.0003',
            'level' => 'detail',
            'parent_id' => 32,
            'built_in' => true,
            'description' => 'Accounts receivable from HART SECURITY',
            'company_id' => 1,
        ],
        [
            'id' => 36,
            'name' => 'Local Customers',
            'type' => 'debtor',
            'code' => '102.01.03',
            'level' => 'general',
            'parent_id' => 19,
            'built_in' => true,
            'description' => 'Accounts receivable from local customers',
            'company_id' => 1,
        ],
        [
            'id' => 37,
            'name' => 'DEYAR DAILY CASH SALES',
            'type' => 'debtor',
            'code' => '102.01.03.0001',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from Deyar daily cash sales',
            'company_id' => 1,
        ],
        [
            'id' => 38,
            'name' => 'DEYAR MEAL CARD SALES',
            'type' => 'debtor',
            'code' => '102.01.03.0002',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from Deyar meal card sales',
            'company_id' => 1,
        ],
        [
            'id' => 39,
            'name' => 'BARBER SHOP',
            'type' => 'debtor',
            'code' => '102.01.03.0003',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from Barber Shop',
            'company_id' => 1,
        ],
        [
            'id' => 40,
            'name' => 'SUPERMARKET',
            'type' => 'debtor',
            'code' => '102.01.03.0004',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from Supermarket',
            'company_id' => 1,
        ],
        [
            'id' => 41,
            'name' => 'IA RESTAURANT & CAFÉ',
            'type' => 'debtor',
            'code' => '102.01.03.0005',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from IA Restaurant & Café',
            'company_id' => 1,
        ],
        [
            'id' => 42,
            'name' => 'TAILOR SHOP',
            'type' => 'debtor',
            'code' => '102.01.03.0006',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from Tailor Shop',
            'company_id' => 1,
        ],
        [
            'id' => 43,
            'name' => 'AFGHAN SOUVENIR SHOP',
            'type' => 'debtor',
            'code' => '102.01.03.0007',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from Afghan Souvenir Shop',
            'company_id' => 1,
        ],
        [
            'id' => 44,
            'name' => 'OTHERS',
            'type' => 'debtor',
            'code' => '102.01.03.0008',
            'level' => 'detail',
            'parent_id' => 36,
            'built_in' => true,
            'description' => 'Accounts receivable from other local customers',
            'company_id' => 1,
        ],
        [
            'id' => 45,
            'name' => 'Inventories Supplies',
            'type' => 'debtor',
            'code' => '103',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Inventories and supplies',
            'company_id' => 1,
        ],
        [
            'id' => 46,
            'name' => 'Office Supplies (Stationery)',
            'type' => 'debtor',
            'code' => '103.01',
            'level' => 'general',
            'parent_id' => 45,
            'built_in' => true,
            'description' => 'Office supplies and stationery',
            'company_id' => 1,
        ],
        [
            'id' => 47,
            'name' => 'IT Supplies',
            'type' => 'debtor',
            'code' => '103.01.01.0001',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'IT supplies',
            'company_id' => 1,
        ],
        [
            'id' => 48,
            'name' => 'Housekeeping Supplies',
            'type' => 'debtor',
            'code' => '103.01.01.0002',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Housekeeping supplies',
            'company_id' => 1,
        ],
        [
            'id' => 49,
            'name' => 'Laundry Supplies',
            'type' => 'debtor',
            'code' => '103.01.01.0003',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Laundry supplies',
            'company_id' => 1,
        ],
        [
            'id' => 50,
            'name' => 'Deyar Food Supplies',
            'type' => 'debtor',
            'code' => '103.01.01.0004',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Deyar food supplies',
            'company_id' => 1,
        ],
        [
            'id' => 51,
            'name' => 'Deyar Supplies (Other Than Food)',
            'type' => 'debtor',
            'code' => '103.01.01.0005',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Deyar supplies other than food',
            'company_id' => 1,
        ],
        [
            'id' => 52,
            'name' => 'Bottled Water Supplies',
            'type' => 'debtor',
            'code' => '103.01.01.0006',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Bottled water supplies',
            'company_id' => 1,
        ],
        [
            'id' => 53,
            'name' => 'Staff Uniforms',
            'type' => 'debtor',
            'code' => '103.01.01.0008',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Staff uniforms',
            'company_id' => 1,
        ],
        [
            'id' => 54,
            'name' => 'Security, Safety & PPE Supplies',
            'type' => 'debtor',
            'code' => '103.01.01.0009',
            'level' => 'detail',
            'parent_id' => 46,
            'built_in' => true,
            'description' => 'Security, safety, and PPE supplies',
            'company_id' => 1,
        ],
        [
            'id' => 55,
            'name' => 'Maintenance & Stock Supplies',
            'type' => 'debtor',
            'code' => '103.02',
            'level' => 'general',
            'parent_id' => 45,
            'built_in' => true,
            'description' => 'Maintenance and stock supplies',
            'company_id' => 1,
        ],
        [
            'id' => 56,
            'name' => 'Electrical',
            'type' => 'debtor',
            'code' => '103.02.01.0001',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Electrical supplies',
            'company_id' => 1,
        ],
        [
            'id' => 57,
            'name' => 'Plumbing',
            'type' => 'debtor',
            'code' => '103.02.01.0002',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Plumbing supplies',
            'company_id' => 1,
        ],
        [
            'id' => 58,
            'name' => 'Carpentry',
            'type' => 'debtor',
            'code' => '103.02.01.0003',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Carpentry supplies',
            'company_id' => 1,
        ],
        [
            'id' => 59,
            'name' => 'HVAC',
            'type' => 'debtor',
            'code' => '103.02.01.0004',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'HVAC supplies',
            'company_id' => 1,
        ],
        [
            'id' => 60,
            'name' => 'Generator Engine Oil & Filters',
            'type' => 'debtor',
            'code' => '103.02.01.0005',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Generator engine oil and filters',
            'company_id' => 1,
        ],
        [
            'id' => 61,
            'name' => 'Generator Maintenance Parts & Supplies',
            'type' => 'debtor',
            'code' => '103.02.01.0006',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Generator maintenance parts and supplies',
            'company_id' => 1,
        ],
        [
            'id' => 62,
            'name' => 'Vehicle Parts & Supplies (Oil & Filters)',
            'type' => 'debtor',
            'code' => '103.02.01.0007',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Vehicle parts and supplies (oil and filters)',
            'company_id' => 1,
        ],
        [
            'id' => 63,
            'name' => 'Outdoor & Landscaping Supplies',
            'type' => 'debtor',
            'code' => '103.02.01.0008',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Outdoor and landscaping supplies',
            'company_id' => 1,
        ],
        [
            'id' => 64,
            'name' => 'Diesel for Generators',
            'type' => 'debtor',
            'code' => '103.02.01.0009',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Diesel for generators',
            'company_id' => 1,
        ],
        [
            'id' => 65,
            'name' => 'Diesel for Company Vehicles',
            'type' => 'debtor',
            'code' => '103.02.01.0010',
            'level' => 'detail',
            'parent_id' => 55,
            'built_in' => true,
            'description' => 'Diesel for company vehicles',
            'company_id' => 1,
        ],
        [
            'id' => 66,
            'name' => 'Prepaid Expenses',
            'type' => 'debtor',
            'code' => '104',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Prepaid expenses',
            'company_id' => 1,
        ],
        [
            'id' => 67,
            'name' => 'Database Software Subscription',
            'type' => 'debtor',
            'code' => '104.01.01.0001',
            'level' => 'detail',
            'parent_id' => 66,
            'built_in' => true,
            'description' => 'Database software subscription',
            'company_id' => 1,
        ],
        [
            'id' => 68,
            'name' => 'Internet Services',
            'type' => 'debtor',
            'code' => '104.01.01.0002',
            'level' => 'detail',
            'parent_id' => 66,
            'built_in' => true,
            'description' => 'Internet services',
            'company_id' => 1,
        ],
        [
            'id' => 69,
            'name' => 'Dish TV Subscription',
            'type' => 'debtor',
            'code' => '104.01.01.0003',
            'level' => 'detail',
            'parent_id' => 66,
            'built_in' => true,
            'description' => 'Dish TV subscription',
            'company_id' => 1,
        ],
        [
            'id' => 70,
            'name' => 'Applications Subscriptions',
            'type' => 'debtor',
            'code' => '104.01.01.0004',
            'level' => 'detail',
            'parent_id' => 66,
            'built_in' => true,
            'description' => 'Applications subscriptions',
            'company_id' => 1,
        ],
        [
            'id' => 71,
            'name' => 'Security Bonds',
            'type' => 'debtor',
            'code' => '104.01.01.0005',
            'level' => 'detail',
            'parent_id' => 66,
            'built_in' => true,
            'description' => 'Security bonds',
            'company_id' => 1,
        ],
        [
            'id' => 72,
            'name' => 'Insurance',
            'type' => 'debtor',
            'code' => '104.01.01.0006',
            'level' => 'detail',
            'parent_id' => 66,
            'built_in' => true,
            'description' => 'Insurance',
            'company_id' => 1,
        ],
        [
            'id' => 73,
            'name' => 'Property, Plant & Equipment',
            'type' => 'debtor',
            'code' => '105',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Property, plant, and equipment',
            'company_id' => 1,
        ],
        [
            'id' => 74,
            'name' => 'Land',
            'type' => 'debtor',
            'code' => '105.01.01.0001',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Land',
            'company_id' => 1,
        ],
        [
            'id' => 75,
            'name' => 'Buildings',
            'type' => 'debtor',
            'code' => '105.01.01.0002',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Buildings',
            'company_id' => 1,
        ],
        [
            'id' => 76,
            'name' => 'Containerized Connex',
            'type' => 'debtor',
            'code' => '105.01.01.0003',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Containerized connex',
            'company_id' => 1,
        ],
        [
            'id' => 77,
            'name' => 'Hangars',
            'type' => 'debtor',
            'code' => '105.01.01.0004',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Hangars',
            'company_id' => 1,
        ],
        [
            'id' => 78,
            'name' => 'Generators',
            'type' => 'debtor',
            'code' => '105.01.01.0005',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Generators',
            'company_id' => 1,
        ],
        [
            'id' => 79,
            'name' => 'Vehicles',
            'type' => 'debtor',
            'code' => '105.01.01.0006',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Vehicles',
            'company_id' => 1,
        ],
        [
            'id' => 80,
            'name' => 'HVAC',
            'type' => 'debtor',
            'code' => '105.01.01.0007',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'HVAC',
            'company_id' => 1,
        ],
        [
            'id' => 81,
            'name' => 'IT Camera, Computers, Laptop, Printer, Scanner, Dish TV',
            'type' => 'debtor',
            'code' => '105.01.01.0008',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'IT equipment',
            'company_id' => 1,
        ],
        [
            'id' => 82,
            'name' => 'Office Furnitures',
            'type' => 'debtor',
            'code' => '105.01.01.0009',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Office furniture',
            'company_id' => 1,
        ],
        [
            'id' => 83,
            'name' => 'Safe Boxes',
            'type' => 'debtor',
            'code' => '105.01.01.0010',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Safe boxes',
            'company_id' => 1,
        ],
        [
            'id' => 84,
            'name' => 'Room Furnitures',
            'type' => 'debtor',
            'code' => '105.01.01.0011',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Room furniture',
            'company_id' => 1,
        ],
        [
            'id' => 85,
            'name' => 'Washers, Dryers & Steamers',
            'type' => 'debtor',
            'code' => '105.01.01.0012',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Washers, dryers, and steamers',
            'company_id' => 1,
        ],
        [
            'id' => 86,
            'name' => 'Cleaning Equipment (Vacuum & Mowers)',
            'type' => 'debtor',
            'code' => '105.01.01.0013',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Cleaning equipment (vacuum and mowers)',
            'company_id' => 1,
        ],
        [
            'id' => 87,
            'name' => 'Maintenance Tools',
            'type' => 'debtor',
            'code' => '105.01.01.0014',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Maintenance tools',
            'company_id' => 1,
        ],
        [
            'id' => 88,
            'name' => 'Security & Safety Tools',
            'type' => 'debtor',
            'code' => '105.01.01.0015',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Security and safety tools',
            'company_id' => 1,
        ],
        [
            'id' => 89,
            'name' => 'Deyar Cooking Equipment',
            'type' => 'debtor',
            'code' => '105.01.01.0016',
            'level' => 'detail',
            'parent_id' => 73,
            'built_in' => true,
            'description' => 'Deyar cooking equipment',
            'company_id' => 1,
        ],
        [
            'id' => 90,
            'name' => 'Liabilities',
            'type' => 'creditor',
            'code' => '200',
            'level' => 'group',
            'parent_id' => null,
            'built_in' => true,
            'description' => 'Payables',
            'company_id' => 1,
        ],
        [
            'id' => 91,
            'name' => 'Accounts Payable',
            'type' => 'creditor',
            'code' => '200.01.01.0001',
            'level' => 'detail',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Accounts payable',
            'company_id' => 1,
        ],
        [
            'id' => 92,
            'name' => 'Vendors Payable',
            'type' => 'creditor',
            'code' => '200.01.01.0002',
            'level' => 'subsidiary',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Vendors payable',
            'company_id' => 1,
        ],
        [
            'id' => 93,
            'name' => 'Salaries Payable',
            'type' => 'creditor',
            'code' => '200.01.01.0003',
            'level' => 'detail',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Salaries payable',
            'company_id' => 1,
        ],
        [
            'id' => 94,
            'name' => 'Staff Accounts',
            'type' => 'creditor',
            'code' => '200.01.01.0004',
            'level' => 'detail',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Staff accounts',
            'company_id' => 1,
        ],
        [
            'id' => 95,
            'name' => 'Taxes Payable',
            'type' => 'creditor',
            'code' => '200.01.01.0005',
            'level' => 'detail',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Taxes payable',
            'company_id' => 1,
        ],
        [
            'id' => 96,
            'name' => 'Bank Loans',
            'type' => 'creditor',
            'code' => '200.01.01.0006',
            'level' => 'detail',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Bank loans',
            'company_id' => 1,
        ],
        [
            'id' => 97,
            'name' => 'Other Payables',
            'type' => 'creditor',
            'code' => '200.01.01.0007',
            'level' => 'detail',
            'parent_id' => 90,
            'built_in' => true,
            'description' => 'Other payables',
            'company_id' => 1,
        ],
        [
            'id' => 98,
            'name' => 'Equity',
            'type' => 'creditor',
            'code' => '300',
            'level' => 'group',
            'parent_id' => null,
            'built_in' => true,
            'description' => 'Owners equity',
            'company_id' => 1,
        ],
        [
            'id' => 99,
            'name' => 'Capital',
            'type' => 'creditor',
            'code' => '300.01.01.0001',
            'level' => 'detail',
            'parent_id' => 98,
            'built_in' => true,
            'description' => 'Capital',
            'company_id' => 1,
        ],
        [
            'id' => 100,
            'name' => 'Common Stock',
            'type' => 'creditor',
            'code' => '300.01.01.0002',
            'level' => 'detail',
            'parent_id' => 98,
            'built_in' => true,
            'description' => 'Common stock',
            'company_id' => 1,
        ],
        [
            'id' => 101,
            'name' => 'Retained Earnings',
            'type' => 'creditor',
            'code' => '300.01.01.0003',
            'level' => 'detail',
            'parent_id' => 98,
            'built_in' => true,
            'description' => 'Retained earnings',
            'company_id' => 1,
        ],
        [
            'id' => 102,
            'name' => 'Share Dividends',
            'type' => 'creditor',
            'code' => '300.01.01.0004',
            'level' => 'detail',
            'parent_id' => 98,
            'built_in' => true,
            'description' => 'Share dividends',
            'company_id' => 1,
        ],
        [
            'id' => 103,
            'name' => 'Income',
            'type' => 'creditor',
            'code' => '400',
            'level' => 'group',
            'parent_id' => null,
            'built_in' => true,
            'description' => 'Income and revenue accounts',
            'company_id' => 1,
        ],
        [
            'id' => 104,
            'name' => 'Accommodations Rent',
            'type' => 'creditor',
            'code' => '400.01.01.0001',
            'level' => 'detail',
            'parent_id' => 103,
            'built_in' => true,
            'description' => 'Accommodations rent income',
            'company_id' => 1,
        ],
        [
            'id' => 105,
            'name' => 'Office Spaces Rent',
            'type' => 'creditor',
            'code' => '400.01.01.0002',
            'level' => 'detail',
            'parent_id' => 103,
            'built_in' => true,
            'description' => 'Office spaces rent income',
            'company_id' => 1,
        ],
        [
            'id' => 106,
            'name' => 'Deyar Daily Cash Sales',
            'type' => 'creditor',
            'code' => '400.01.01.0003',
            'level' => 'detail',
            'parent_id' => 103,
            'built_in' => true,
            'description' => 'Deyar daily cash sales income',
            'company_id' => 1,
        ],
        [
            'id' => 107,
            'name' => 'Deyar Meal Card Sales',
            'type' => 'creditor',
            'code' => '400.01.01.0004',
            'level' => 'detail',
            'parent_id' => 103,
            'built_in' => true,
            'description' => 'Deyar meal card sales income',
            'company_id' => 1,
        ],
        [
            'id' => 108,
            'name' => 'Other Income',
            'type' => 'creditor',
            'code' => '400.01.01.0005',
            'level' => 'detail',
            'parent_id' => 103,
            'built_in' => true,
            'description' => 'Other income',
            'company_id' => 1,
        ],
        [
            'id' => 109,
            'name' => 'Expenses',
            'type' => 'debtor',
            'code' => '500',
            'level' => 'group',
            'parent_id' => null,
            'built_in' => true,
            'description' => 'Expenses',
            'company_id' => 1,
        ],
        [
            'id' => 110,
            'name' => 'Operating, Administrative, Consultancies, Commissions',
            'type' => 'debtor',
            'code' => '500.01',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Operating, administrative, consultancies, and commissions expenses',
            'company_id' => 1,
        ],
        [
            'id' => 111,
            'name' => 'Salaries',
            'type' => 'debtor',
            'code' => '500.01.01.0001',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Salaries expenses',
            'company_id' => 1,
        ],
        [
            'id' => 112,
            'name' => 'Consultancies',
            'type' => 'debtor',
            'code' => '500.01.01.0002',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Consultancies expenses',
            'company_id' => 1,
        ],
        [
            'id' => 113,
            'name' => 'Business Development & Marketing',
            'type' => 'debtor',
            'code' => '500.01.01.0003',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Business development and marketing expenses',
            'company_id' => 1,
        ],
        [
            'id' => 114,
            'name' => 'Taxes',
            'type' => 'debtor',
            'code' => '500.01.01.0004',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Taxes expenses',
            'company_id' => 1,
        ],
        [
            'id' => 115,
            'name' => 'Charities & Grants',
            'type' => 'debtor',
            'code' => '500.01.01.0005',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Charities and grants expenses',
            'company_id' => 1,
        ],
        [
            'id' => 116,
            'name' => 'Depreciation Expense',
            'type' => 'debtor',
            'code' => '500.01.01.0006',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Depreciation expense',
            'company_id' => 1,
        ],
        [
            'id' => 117,
            'name' => 'Bank Fees',
            'type' => 'debtor',
            'code' => '500.01.01.0007',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Bank fees',
            'company_id' => 1,
        ],
        [
            'id' => 118,
            'name' => 'Legal Expenses',
            'type' => 'debtor',
            'code' => '500.01.01.0008',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Legal expenses',
            'company_id' => 1,
        ],
        [
            'id' => 119,
            'name' => 'Visa Expense',
            'type' => 'debtor',
            'code' => '500.01.01.0009',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Visa expense',
            'company_id' => 1,
        ],
        [
            'id' => 120,
            'name' => 'Tickets & Travel Expense',
            'type' => 'debtor',
            'code' => '500.01.01.0010',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Tickets and travel expense',
            'company_id' => 1,
        ],
        [
            'id' => 121,
            'name' => 'Miscellaneous',
            'type' => 'debtor',
            'code' => '500.01.01.0011',
            'level' => 'detail',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Miscellaneous expenses',
            'company_id' => 1,
        ],
        [
            'id' => 122,
            'name' => 'Utilities & Rentals',
            'type' => 'debtor',
            'code' => '500.02',
            'level' => 'general',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Utilities and rentals expenses',
            'company_id' => 1,
        ],
        [
            'id' => 123,
            'name' => 'Electricity',
            'type' => 'debtor',
            'code' => '500.02.01.0001',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Electricity expenses',
            'company_id' => 1,
        ],
        [
            'id' => 124,
            'name' => 'Water',
            'type' => 'debtor',
            'code' => '500.02.01.0002',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Water expenses',
            'company_id' => 1,
        ],
        [
            'id' => 125,
            'name' => 'Septic',
            'type' => 'debtor',
            'code' => '500.02.01.0003',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Septic expenses',
            'company_id' => 1,
        ],
        [
            'id' => 126,
            'name' => 'Garbage',
            'type' => 'debtor',
            'code' => '500.02.01.0004',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Garbage expenses',
            'company_id' => 1,
        ], [
            'id' => 127,
            'name' => 'PEST CONTROL',
            'type' => 'debtor',
            'code' => '500.02.01.0005',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Garbage expenses',
            'company_id' => 1,
        ], [
            'id' => 128,
            'name' => 'INTERNET',
            'type' => 'debtor',
            'code' => '500.02.01.0006',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Garbage expenses',
            'company_id' => 1,
        ], [
            'id' => 129,
            'name' => 'i2 Rent',
            'type' => 'debtor',
            'code' => '500.02.01.0007',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Garbage expenses',
            'company_id' => 1,
        ],[
            'id' => 130,
            'name' => 'i3 Rent',
            'type' => 'debtor',
            'code' => '500.02.01.0008',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Garbage expenses',
            'company_id' => 1,
        ],[
            'id' => 131,
            'name' => 'i4 Rent',
            'type' => 'debtor',
            'code' => '500.02.01.0009',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Garbage expenses',
            'company_id' => 1,
        ],
        [
            'id' => 132,
            'name' => 'Equipment & Machinery Rentals',
            'type' => 'debtor',
            'code' => '500.02.01.0010',
            'level' => 'detail',
            'parent_id' => 122,
            'built_in' => true,
            'description' => 'Equipment and machinery rentals expenses',
            'company_id' => 1,
        ],
        [
            'id' => 133,
            'name' => 'Maintenance & Stock Supplies',
            'type' => 'debtor',
            'code' => '500.03',
            'level' => 'general',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Maintenance and stock supplies expenses',
            'company_id' => 1,
        ],

        [
            'id' => 134,
            'name' => 'Electrical',
            'type' => 'debtor',
            'code' => '500.03.01.0001',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Electrical maintenance expenses',
            'company_id' => 1,
        ],
        [
            'id' => 135,
            'name' => 'Plumbing',
            'type' => 'debtor',
            'code' => '500.03.01.0002',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Plumbing maintenance expenses',
            'company_id' => 1,
        ],
        [
            'id' => 136,
            'name' => 'Carpentry',
            'type' => 'debtor',
            'code' => '500.03.01.0003',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Carpentry maintenance expenses',
            'company_id' => 1,
        ],
        [
            'id' => 137,
            'name' => 'HVAC',
            'type' => 'debtor',
            'code' => '500.03.01.0004',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'HVAC maintenance expenses',
            'company_id' => 1,
        ],
        [
            'id' => 138,
            'name' => 'Generator Parts & Supplies',
            'type' => 'debtor',
            'code' => '500.03.01.0005',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Generator parts and supplies expenses',
            'company_id' => 1,
        ],
        [
            'id' => 139,
            'name' => 'Vehicle Parts & Supplies (Oil & Filters)',
            'type' => 'debtor',
            'code' => '500.03.01.0006',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Vehicle parts and supplies (oil and filters) expenses',
            'company_id' => 1,
        ],
        [
            'id' => 140,
            'name' => 'Outdoor Greenery',
            'type' => 'debtor',
            'code' => '500.03.01.0007',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Outdoor greenery maintenance expenses',
            'company_id' => 1,
        ],
        [
            'id' => 141,
            'name' => 'Diesel for Generators',
            'type' => 'debtor',
            'code' => '500.03.01.0008',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Diesel for generators expenses',
            'company_id' => 1,
        ],
        [
            'id' => 142,
            'name' => 'Diesel for Company Vehicles',
            'type' => 'debtor',
            'code' => '500.03.01.0009',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Diesel for company vehicles expenses',
            'company_id' => 1,
        ],
        [
            'id' => 143,
            'name' => 'Repairs & Maintenance',
            'type' => 'debtor',
            'code' => '500.03.01.0010',
            'level' => 'detail',
            'parent_id' => 133,
            'built_in' => true,
            'description' => 'Repairs and maintenance expenses',
            'company_id' => 1,
        ],
        [
            'id' => 144,
            'name' => 'Leasehold Improvements & Renovations',
            'type' => 'debtor',
            'code' => '500.04',
            'level' => 'general',
            'parent_id' => 109,
            'built_in' => true,
            'description' => 'Leasehold improvements and renovations expenses',
            'company_id' => 1,
        ],
        [
            'id' => 145,
            'name' => 'Accommodation Room Renovations',
            'type' => 'debtor',
            'code' => '500.04.01.0001',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'Accommodation room renovations expenses',
            'company_id' => 1,
        ],
        [
            'id' => 146,
            'name' => 'ECP1 Relocation & Improvements',
            'type' => 'debtor',
            'code' => '500.04.01.0002',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'ECP1 relocation and improvements expenses',
            'company_id' => 1,
        ],
        [
            'id' => 147,
            'name' => 'External UN Compound Improvements',
            'type' => 'debtor',
            'code' => '500.04.01.0003',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'External UN compound improvements expenses',
            'company_id' => 1,
        ],
        [
            'id' => 148,
            'name' => 'FAO Facilities Renovations',
            'type' => 'debtor',
            'code' => '500.04.01.0004',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'FAO facilities renovations expenses',
            'company_id' => 1,
        ],
        [
            'id' => 149,
            'name' => 'UNHCR Facilities Renovations',
            'type' => 'debtor',
            'code' => '500.04.01.0005',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'UNHCR facilities renovations expenses',
            'company_id' => 1,
        ],
        [
            'id' => 150,
            'name' => 'Deyar Restaurant Renovations',
            'type' => 'debtor',
            'code' => '500.04.01.0006',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'Deyar restaurant renovations expenses',
            'company_id' => 1,
        ],
        [
            'id' => 151,
            'name' => 'Office Renovations',
            'type' => 'debtor',
            'code' => '500.04.01.0007',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'Office renovations expenses',
            'company_id' => 1,
        ],
        [
            'id' => 152,
            'name' => 'Laundry Facility',
            'type' => 'debtor',
            'code' => '500.04.01.0008',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'Laundry facility expenses',
            'company_id' => 1,
        ],
        [
            'id' => 153,
            'name' => '14 Sports Complex Improvements',
            'type' => 'debtor',
            'code' => '500.04.01.0009',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => '14 Sports complex improvements expenses',
            'company_id' => 1,
        ],
        [
            'id' => 154,
            'name' => 'Fitness Facility',
            'type' => 'debtor',
            'code' => '500.04.01.0010',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'Fitness facility expenses',
            'company_id' => 1,
        ],
        [
            'id' => 155,
            'name' => 'Social Room',
            'type' => 'debtor',
            'code' => '500.04.01.0011',
            'level' => 'detail',
            'parent_id' => 144,
            'built_in' => true,
            'description' => 'Social room expenses',
            'company_id' => 1,
        ],[
            'id' => 156,
            'name' => 'Bank',
            'type' => 'debtor',
            'code' => '106',
            'level' => 'general',
            'parent_id' => 1,
            'built_in' => true,
            'description' => 'Social room expenses',
            'company_id' => 1,
        ],
    ];
    \App\Models\Account::query()->forceDelete();
    foreach ($accounts as $account) {
            $account['stamp']=$account['name'];
            $account['currency_id']=1;
            $account['group']='Asset';
        \Illuminate\Support\Facades\DB::table('accounts')->insert($account);
    }
});
