<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\AssetEmployeeItem;
use App\Models\Bid;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\FinancialPeriod;
use App\Models\Grn;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Person;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Separation;
use App\Models\TakeOut;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\Typeleave;
use App\Models\UrgentLeave;
use App\Models\VisitorRequest;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use function PHPUnit\Framework\isNull;

class PdfController extends Controller
{
    protected $period;
    public function payroll($id,$title)
    {

        $payroll = Payroll::query()->with('employee', 'itemAllowances', 'itemDeductions', 'benefits')->findOrFail($id);
        $company = $payroll->company;

        $pdf = Pdf::loadView('pdf.payroll', compact('payroll', 'company','title'));
        return $pdf->stream('pdf.payroll');
    }

    public function jornal($transactions, Request $request)
    {
        $company = auth()->user()->employee->company;
        $transactions = Transaction::query()->whereIn('id', explode('-', $transactions))->get();
        // dd($transactions);

        $pdf = Pdf::loadView('pdf.jornal', compact('transactions', 'company'));
        return $pdf->stream('jornal.pdf');
    }
    public function leaverequest(Request $request , $id)
    {
        $company = auth()->user()->employee->company;
        $leave = Leave::query()->with(['employee','company','typeLeave'])->findOrFail($id);
        $holidays=Holiday::query()->where('company_id',$company->id)->get()->toArray();
        $types=Typeleave::query()->where('company_id',$company->id)->orderBy('sort')->get();

        $lastleave = Leave::query()
            ->where('employee_id', $leave->employee->id)->where('id','<',$leave->id)
            ->orderBy('id', 'desc')
            ->first();


        $pdf = Pdf::loadView('pdf.leaverequest',compact('types','company','leave','lastleave','holidays'));
        return $pdf->stream('leaverequest.pdf');
    }
     public function overtime(Request $request , $id)
    {
        $company = auth()->user()->employee->company;
        $overtime = Overtime::query()->findOrFail($id);
        // $lastleave = Leave::query()->where('employee_id',$leave->employee->id)->first();
        // dd($company);

        $pdf = Pdf::loadView('pdf.overtime',compact('company','overtime'));
        return $pdf->stream('overtime.pdf');
    }

    public function account($period, $account, Request $request)
    {
        $company = auth()->user()->employee->company;
        $startDate = null;
        $endDate = null;
        $accounts = explode('-', $account);
        $accountTitle = $request->reportTitle ?? implode('-', Account::query()->whereIn('id', $accounts)->pluck('name')->toArray());

        $Allaccounts =  Account::query()->whereIn('id', $accounts)
            ->orWhereIn('parent_id', $accounts)
            ->orWhereHas('account', function ($query) use ($accounts) {
                return $query->whereIn('parent_id', $accounts)->orWhereHas('account', function ($query) use ($accounts) {
                    return $query->whereIn('parent_id', $accounts);
                });
            })
            ->get()->pluck('id')->toArray();

        if (isset($request->date)) {
            $dateRange = $request->date;
            [$startDate, $endDate] = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
            $endDate = Carbon::createFromFormat('d-m-Y', $endDate);

            $transactions = Transaction::query()->with(['invoice','account'])->where('financial_period_id', $period)->whereIn('account_id', $Allaccounts)->whereHas('invoice', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })->get();
        } else {
            $transactions = Transaction::query()->where('financial_period_id', $period)->whereIn('account_id', $Allaccounts)->get();
        }

        $transactions = $transactions->sortBy(function ($transaction) {
            return $transaction->invoce_id;
        });
        $period =  FinancialPeriod::query()->find($period);
        $pdf = Pdf::loadView(
            'pdf.account',
            compact('accountTitle', 'accounts', 'period', 'transactions', 'startDate', 'endDate', 'company')
        );
        return $pdf->stream('account.pdf');
    }
    public function accountCurrency($period, $account, Request $request)
    {
        $company = auth()->user()->employee->company;
        $startDate = null;
        $endDate = null;
        $accounts = explode('-', $account);
        $accountTitle = $request->reportTitle ?? implode('-', Account::query()->whereIn('id', $accounts)->pluck('name')->toArray());

        $Allaccounts =  Account::query()->whereIn('id', $accounts)
            ->orWhereIn('parent_id', $accounts)
            ->orWhereHas('account', function ($query) use ($accounts) {
                return $query->whereIn('parent_id', $accounts)->orWhereHas('account', function ($query) use ($accounts) {
                    return $query->whereIn('parent_id', $accounts);
                });
            })
            ->get()->pluck('id')->toArray();

        if (isset($request->date)) {
            $dateRange = $request->date;
            [$startDate, $endDate] = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
            $endDate = Carbon::createFromFormat('d-m-Y', $endDate);

            $transactions = Transaction::query()->where('financial_period_id', $period)->whereIn('account_id', $Allaccounts)->whereHas('invoice', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })->get();
        } else {
            $transactions = Transaction::query()->where('financial_period_id', $period)->whereIn('account_id', $Allaccounts)->get();
        }

        $transactions = $transactions->sortBy(function ($transaction) {
            return $transaction->invoce_id;
        });
        $period =  FinancialPeriod::query()->find($period);
        $pdf = Pdf::loadView(
            'pdf.accountCurrency',
            compact('accountTitle', 'accounts', 'period', 'transactions', 'startDate', 'endDate', 'company')
        );
        return $pdf->stream('account.accountCurrency');
    }
    private function calculateAccountTree($group, $request)
    {
        $startDate = Carbon::parse($this->period->start_date);
        $endDate = isset($request->date) ? Carbon::createFromFormat('Y-m-d', $request->date) : null;

        // محاسبه sum برای این گروه
        $sum = $group
            ->where('id', $group->id)->orWhere('parent_id', $group->id)
            ->orWhereHas('account', function ($query) use ($group) {
                return $query->where('parent_id', $group->id)->orWhereHas('account', function ($query) use ($group) {
                    return $query->where('parent_id', $group->id);
                });
            })
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                if ($account->type == 'debtor') {
                    return $account->transactions()
                            ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                            })
                            ->where('financial_period_id', $this->period->id)->sum('debtor')
                        -
                        $account->transactions()
                            ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                            })->where('financial_period_id', $this->period->id)->sum('creditor');
                } elseif ($account->type == 'creditor') {
                    return $account->transactions()
                            ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                            })->where('financial_period_id', $this->period->id)->sum('creditor')
                        -
                        $account->transactions()
                            ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                            })->where('financial_period_id', $this->period->id)->sum('debtor');
                }
            })->sum();

        // بازگشتی برای childerns
        $items = $group->childerns->pluck(null, 'stamp')->map(function ($child) use ($request) {
            return $this->calculateAccountTree($child, $request);
        });

        return [
            'sum' => $sum,
            'item' => $items
        ];
    }


    public function balance($period, Request $request)
    {
        $this->period = null;
        $company = auth()->user()->employee->company;
        $endDate = null;
        if (isset($request->date)) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->date);
                // $endDate = $request->    date;

            } catch (\Exception $e) {
                abort(404, 'Invalid parameters provided.');
            }
        }

        try {
            $this->period = FinancialPeriod::query()
                ->where('company_id', $company->id)
                ->find($period);
        } catch (\Exception $e) {
            abort(404, 'Invalid parameters provided.');
        }

        $accounts =  $company->accounts
            ->where('level', 'main')
            ->pluck(null, 'stamp')
            ->filter(function ($account) {

                return in_array($account->level, ['main', 'group', 'general']);
            })
            ->map(function ($group) use ($request) {
                return [
                    $group->stamp => $this->calculateAccountTree($group, $request)
                ];
            });

        // dd($accounts);


        $pdf = Pdf::loadView(
            'pdf.balance',
            compact('accounts', 'company', 'endDate')
        );
        return $pdf->stream('balance.pdf');
    }
    public function PL($period, Request $request)
    {
        $financialPeriod = FinancialPeriod::query()
            ->findOrFail($period);
        $company = $financialPeriod->company;
        $endDate = null;

        if ($request->date) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->date);
            } catch (\Exception $e) {
                abort(404, 'Invalid date format.');
            }
        }



        $accounts = $company->accounts()
            ->with(['childrenRecursive.transactions' => function ($q) use ($financialPeriod) {
                $q->where('financial_period_id', $financialPeriod);
            }])
            ->whereIn('group', ['Income', 'Expense'])
            ->get();

        // محاسبه مجموع‌ها
        $report = [
            'Income' => [],
            'Expense' => [],
            'IncomeTotal' => 0,
            'ExpensesTotal' => 0,
        ];

        foreach ($accounts as $account) {
            $debitSum = $account->transactions()
                ->sum('debtor');

            $creditSum = $account->transactions()
                ->sum('creditor');

            $sum =   $creditSum-$debitSum;
            $report[$account->group][$account->name] = abs($sum);

            if ($account->group === 'Income') {
                $report['IncomeTotal'] += $sum;
            } elseif ($account->group === 'Expense') {
                $report['ExpensesTotal'] += abs($sum );
            }
        }

        $report['NetProfit'] = $report['IncomeTotal'] - $report['ExpensesTotal'];
        // ایجاد PDF
        $pdf = Pdf::loadView('pdf.P&L', [
            'report' => $report,
            'company' => $company,
            'endDate' => $endDate,
        ]);

        return $pdf->stream();
    }




    public function document($document)
    {
        $company = auth()->user()->employee->company;
        $document = Invoice::find($document);
        $pdf = Pdf::loadView(
            'pdf.document',
            compact('document', 'company')
        );
        return $pdf->stream('document.pdf');
    }

    public function trialBalance($period, Request $request)
    {
        $this->period = null;
        // dd($request->date,, Carbon::createFromFormat('d-m-Y', $request->date));
        $endDate = null;
        if (isset($request->date)) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->date);
                // $endDate = $request->    date;

            } catch (\Exception $e) {
                abort(404, 'Invalid parameters provided.');
            }
        }


        $company = auth()->user()->employee->company;

        try {
            $this->period = FinancialPeriod::query()
                ->where('company_id', $company->id)
                ->find($period);
            $this->period->id;
        } catch (\Exception $e) {
            abort(404, 'Invalid parameters provided.');
        }


        // dd($this->period);
        $accounts = Account::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->whereHas('transactions', function ($query) use ($request) {
                $query->where('financial_period_id', $this->period->id);


                if (isset($request->date)) {

                    $startDate = Carbon::parse($this->period->start_date);
                    $endDate = Carbon::createFromFormat('Y-m-d', $request->date);
                    // dd( $endDate);
                    $query->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                        $invoiceQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
                }
            })
            ->get();


        $pdf = Pdf::loadView(
            'pdf.trialBalance',
            compact('accounts', 'company', 'endDate')
        );
        return $pdf->stream('trialBalance.pdf');
    }

    public function employee($id)
    {
        $employee =  Employee::query()->with(['department','position','duty','contract','media','company'])->findOrFail($id);
        $pdf = Pdf::loadView(
            'pdf.employee',
            compact('employee')
        );
        return $pdf->stream('employee.pdf');
    }
    public function payrolls($ids)
    {

        $payrolls =  Payroll::query()->whereIn('id', explode('-', $ids))->get() ->groupBy(fn($payroll) => $payroll->employee->department->title);

        $company = auth()->user()->employee->company;
        $pdf = Pdf::loadView(
            'pdf.payrolls',
            compact('payrolls', 'company'),
            [],
            ['format' => 'A4-L']
        );
        return $pdf->stream('payrolls.pdf');
    }

    public function purchase($id)
    {
        $pr = PurchaseRequest::query()->with(['company', 'items'])->findOrFail($id);
        $company = $pr->company;

        $pdf = Pdf::loadView(
            'pdf.purchase',
            compact('company', 'pr')
        );
        return $pdf->stream('purchase.pdf');
    }
    public function purchaseOrder($id)
    {
        $po = PurchaseOrder::query()->with(['company', 'items'])->findOrFail($id);
        $company = $po->company;

        $pdf = Pdf::loadView(
            'pdf.po',
            compact('company', 'po')
        );
        return $pdf->stream('purchase.pdf');
    }
    public function quotation($id)
    {
        $pr = PurchaseRequest::query()->with(['company', 'items'])->findOrFail($id);
        $company = auth()->user()->employee->company;


        $pdf = Pdf::loadView(
            'pdf.quotation',
            compact('company', 'pr')
        );
        return $pdf->stream('quotation.pdf');
    }
    public function bid($id)
    {
        $bid = Bid::query()->with(['company'])->findOrFail($id);
        $company = auth()->user()->employee->company;
        $PR = $bid->purchaseRequest;


        $pdf = Pdf::loadView(
            'pdf.bid',
            compact('company', 'bid', 'PR')
        );
        return $pdf->stream('bid.pdf');
    }
    public function separation($id)
    {
        $employee = Employee::query()->findOrFail($id);
        $company = $employee->company;


        $pdf = Pdf::loadView(
            'pdf.separation',
            compact('company', 'employee')
        );
        return $pdf->stream('separation.pdf');
    }
    public function takeOut($id)
    {
        $takeOut = TakeOut::query()->findOrFail($id);
        $company = auth()->user()->employee->company;


        $pdf = Pdf::loadView(
            'pdf.takeOut',
            compact('company', 'takeOut')
        );
        return $pdf->stream('takeOut.pdf');
    }
    public function requestVisit($id)
    {
        $requestVisit = VisitorRequest::query()->firstWhere('id', $id);
        $company = $requestVisit->company;


        $pdf = Pdf::loadView(
            'pdf.requestVisit',
            compact('company', 'requestVisit')
        );
        return $pdf->stream('requestVisit.pdf');
    }
    public function entryAndExit($id)
    {
        $requestVisit = VisitorRequest::query()->firstWhere('id', $id);
        $company = $requestVisit->company;


        $pdf = Pdf::loadView(
            'pdf.entry_and_exit',
            compact('company', 'requestVisit')
        );
        return $pdf->stream('requestVisit.pdf');
    }
    public function cashPayment($id)
    {

        $invoice = Invoice::query()->findOrFail($id);

        $company = $invoice->company;
// dd($invoice);
        $pdf = Pdf::loadView(
            'pdf.cashPayment',
            compact('company','invoice')
        );
        return $pdf->stream('cashPayment.pdf');
    }
     public function clearance($id,$company)
    {
        $clearance=Separation::query()->with('employee')->findOrFail($id);
        $company = Company::query()->findOrFail($company);


        $pdf = Pdf::loadView(
            'pdf.clearance',
            compact('company','clearance')
        );
        return $pdf->stream('clearance.pdf');
    }
    public function requestVisits($ids)
    {

        $requestVisits = VisitorRequest::query()->whereIn('id', explode('-', $ids))->orderBy('id', 'desc')->get();
        $company = $requestVisits[0]?->company;


        $pdf = Pdf::loadView(
            'pdf.requestVisits',
            compact('company', 'requestVisits')
        );
        return $pdf->stream('requestVisits.pdf');
    }

    public function assets($ids,$company,$type)
    {
        $assets = Asset::query()->with(['product', 'employees','warehouse','checkOutTo','person'])->whereIn('id', explode('-', $ids))->get()->groupBy($type);
        $company = Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView(
            'pdf.assets',
            compact('company', 'assets','type')
        );
        return $pdf->stream('pdf.assets');
    }

    public function asset($id)
    {
        $asset = Asset::query()->with(['product', 'employees'])->where('id',  $id)->firstOrFail();
        // dd($asset);
        $company = $asset->company;
        $pdf = Pdf::loadView(
            'pdf.asset',
            compact('company', 'asset')
        );
        return $pdf->stream('pdf.asset');
    }
    public function tasks($ids)
    {
        $tasks = Task::query()->with(['employees'])->whereIn('id', explode('-', $ids))->orderBy('id', 'desc')->get();
        $company = $tasks[0]->company;
        $pdf = Pdf::loadView(
            'pdf.tasks',
            compact('company', 'tasks')
        );
        return $pdf->stream();
    }

    public function barcode($code)
    {

        $pdf = Pdf::loadView(
            'pdf.barcode',
            compact( 'code'),[], [
                'format' => [50, 35],
            ]
        );
        return $pdf->stream();
    }
    public function barcodes($codes)
    {

        $pdf = Pdf::loadView(
            'pdf.barcodes',
            compact( 'codes'),[],
        );
        return $pdf->stream();
    }
    public function qrcodeView($code)
    {
        $companyID=Asset::query()->firstWhere('id',$code)?->company_id;
        $pdf = Pdf::loadView(
            'pdf.qrcodeview',
            compact( 'code','companyID'),[],
            [
                'format' => [80, 80],
            ]
        );
        return $pdf->stream();
    }
    public function qrcode($code)
    {
        $companyID=Asset::query()->firstWhere('id',$code)?->company_id;

        $pdf = Pdf::loadView(
            'pdf.qrcode',
            compact( 'code','companyID'),[],
            [
                'format' => [80, 80],
            ]
        );
        return $pdf->stream();
    }
    public function loan($id)
    {
        $loan=Loan::query()->with(['company'])->findOrFail($id);
        $company=$loan->company;
        $pdf = Pdf::loadView(
            'pdf.loan',
            compact( 'loan','company')
        );
        return $pdf->stream();
    }
    public function cashAdvance($id)
    {
        $loan=Loan::query()->with(['company','employee','employee.manager','employee.department','employee.media','admin','finance'])->findOrFail($id);
        $company=$loan->company;
        $pdf = Pdf::loadView(
            'pdf.cashAdvance',
            compact( 'loan','company')
        );
        return $pdf->stream();
    }
    public function sales($id)
    {

        $invoice=Factor::query()->with(['items','items.unit','company'])->findOrFail($id);
        $company=$invoice->company;
        $pdf = Pdf::loadView(
            'pdf.sales',
            compact( 'company','invoice')
        );
        return $pdf->stream();
    }
    public function urgentleave($id)
    {
        $urgent=UrgentLeave::query()->with('company')->findOrFail($id);
        $company = $urgent->company;

        $pdf = Pdf::loadView('pdf.urgentleave',compact( 'company','urgent'));
        return $pdf->stream('urgentleave.pdf');
    }
    public function personals($company)
    {
        $groups = Person::query()->where('company_id',$company)->get()->groupBy('person_group');
        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.persons', compact('groups','company'));
        return $pdf->stream('personnel_details.pdf');
    }
    public function assetsBalance($ids,$company)
    {
        $groups = Warehouse::query()->with('assets')->whereIn('id',explode('-',$ids))->get();
        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.assets-balance', compact('groups','company'));
        return $pdf->stream('assets-balance.pdf');
    }
    public function audit($ids,$company)
    {
        $assets = Asset::query()->with('warehouse')->whereIn('id',explode('-',$ids))->get()->groupBy('warehouse_id');
        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.audit', compact('assets','company'));
        return $pdf->stream();
    }
    public function auditChecklist($company,$type)
    {
        $groups = Asset::query()->with(['warehouse','brand','product'])->where('company_id',$company)->get()->groupBy('warehouse_id');

        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.audit-checklist', compact('groups','company','type'));
        return $pdf->stream();
    }
    public function employeeAssetHistory($id,$type,$company)
    {
        $histories=null;
        if ($type=="ID"){
            $histories = AssetEmployee::query()->with(['assetEmployeeItem'])->firstWhere('id',$id);
        }elseif ($type==="Personnel"){

            $histories = AssetEmployee::query()->with(['assetEmployeeItem'])->firstWhere('person_id',$id);

        }
        if ($histories===null){

            abort(404);
        }
        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.employeeAssetHistory', compact('histories','company'));
        return $pdf->stream();
    }
    public function employeeAsset($id,$type,$company)
    {
        $histories=null;
        $record=AssetEmployee::query()->firstWhere('id',$id);
        if ($type=="ID"){
            $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                ->whereHas('assetEmployee', function ($q)use($record) {
                    if ($record->employee_id){
                        $q->where('employee_id', $record->employee_id);
                    }else{
                        $q->where('person_id', $record->person_id);
                    }
                })
                ->groupBy('asset_id');

           $histories=  AssetEmployeeItem::query()
                ->whereIn('id', $sub)
                ->where('type', 'Assigned')->get();

        }elseif ($type==="Personnel"){

            $histories = AssetEmployee::query()->with(['assetEmployeeItem'])->firstWhere('person_id',$id);

        }
        if ($histories===null){

            abort(404);
        }
        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.employeeAsset', compact('record','histories','company'));
        return $pdf->stream();
    }

    public function grn($id,$company){
        $GRN=Grn::query()->with(['items','manager','purchaseOrder','purchaseOrder'])->findOrFail($id);
        $company=Company::query()->firstWhere('id',$company);
        $pdf = Pdf::loadView('pdf.grn', compact('GRN','company'));
        return $pdf->stream();
    }
}
