<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Bid;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\FinancialPeriod;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Person;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
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

class PdfController extends Controller
{
    protected $period;
    public function payroll($id)
    {

        $payroll = Payroll::query()->with('employee', 'itemAllowances', 'itemDeductions', 'benefits')->findOrFail($id);
        $company = $payroll->company;

        $pdf = Pdf::loadView('pdf.payroll', compact('payroll', 'company'));
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
            ->where('employee_id', $leave->employee->id)
            ->orderBy('id', 'desc')
            ->skip(1)
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
            $this->period->id;
        } catch (\Exception $e) {
            abort(404, 'Invalid parameters provided.');
        }

        $accounts = $company->accounts
            ->where('level', 'main')
            ->pluck(null, 'stamp')
            ->map(function ($group) use ($request) {
                return
                    [$group->stamp => [
                        'sum' =>
                        $group
                            ->where('id', $group->id)->orWhere('parent_id', $group->id)
                            ->orWhereHas('account', function ($query) use ($group) {
                                return $query->where('parent_id', $group->id)->orWhereHas('account', function ($query) use ($group) {
                                    return $query->where('parent_id', $group->id);
                                });
                            })
                            ->get()->map(function ($account) use ($request) {
                                $endDate = isset($request->date) ? Carbon::createFromFormat('Y-m-d', $request->date) : null;

                                $startDate = Carbon::parse($this->period->start_date);

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
                            })->sum(),
                        'item' => $group->childerns->pluck(null, 'stamp')->map(function ($child) use ($request) {
                            return [
                                'sum' => $child
                                    ->where('id', $child->id)->orWhere('parent_id', $child->id)
                                    ->orWhereHas('account', function ($query) use ($child) {
                                        return $query->where('parent_id', $child->id)->orWhereHas('account', function ($query) use ($child) {
                                            return $query->where('parent_id', $child->id);
                                        });
                                    })
                                    ->get()->map(function ($account) use ($request) {
                                        $endDate = isset($request->date) ? Carbon::createFromFormat('Y-m-d', $request->date) : null;

                                        $startDate = Carbon::parse($this->period->start_date);

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
                                    })->sum(),
                                'item' => $child->childerns->pluck(null, 'stamp')->map(function ($item) use ($request) {
                                    return $item
                                        ->where('id', $item->id)->orWhere('parent_id', $item->id)
                                        ->orWhereHas('account', function ($query) use ($item) {
                                            return $query->where('parent_id', $item->id)->orWhereHas('account', function ($query) use ($item) {
                                                return $query->where('parent_id', $item->id);
                                            });
                                        })
                                        ->get()->pluck(null, 'name')
                                        ->map(function ($itemaccount) use ($request) {
                                            $endDate = isset($request->date) ? Carbon::createFromFormat('Y-m-d', $request->date) : null;
                                            $startDate = Carbon::parse($this->period->start_date);

                                            if ($itemaccount->type == 'debtor') {
                                                return $itemaccount

                                                    ->transactions()
                                                    ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                                        $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                                        if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                                                    })
                                                    ->where('financial_period_id', $this->period->id)->sum('debtor')
                                                    -
                                                    $itemaccount

                                                    ->transactions()
                                                    ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                                        $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                                        if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                                                    })
                                                    ->where('financial_period_id', $this->period->id)->sum('creditor');
                                            } elseif ($itemaccount->type == 'creditor') {
                                                return $itemaccount

                                                    ->transactions()
                                                    ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                                        $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                                        if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                                                    })->where('financial_period_id', $this->period->id)->sum('creditor')
                                                    -
                                                    $itemaccount

                                                    ->transactions()
                                                    ->whereHas('invoice', function ($invoiceQuery) use ($startDate, $endDate) {
                                                        $invoiceQuery->whereDate('date', '>=', $startDate->toDateString());
                                                        if ($endDate) $invoiceQuery->whereDate('date', '<=', $endDate->toDateString());
                                                    })->where('financial_period_id', $this->period->id)->sum('debtor');
                                            }
                                        })->sum();
                                })
                            ];
                        })
                    ]];
            });

        // dd($accounts);


        $pdf = Pdf::loadView(
            'pdf.balance',
            compact('accounts', 'company', 'endDate')
        );
        return $pdf->stream('balance.pdf');
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
        $employee =  Employee::query()->findOrFail($id);
        $pdf = Pdf::loadView(
            'pdf.employee',
            compact('employee')
        );
        return $pdf->stream('employee.pdf');
    }
    public function payrolls($ids)
    {

        $payrolls =  Payroll::query()->whereIn('id', explode('-', $ids))->get();
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
     public function clearance($ids)
    {

        $company = auth()->user()->employee->company;


        $pdf = Pdf::loadView(
            'pdf.clearance',
            compact('company')
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
}
