<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Bid;
use App\Models\Company;
use App\Models\Employee;
use App\Models\FinancialPeriod;
use App\Models\Payroll;
use App\Models\Invoice;
use App\Models\PurchaseRequest;
use App\Models\TakeOut;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \niklasravnsborg\LaravelPdf\Facades\Pdf;

class PdfController extends Controller
{
    protected $period;
    public function payroll($id)
    {

        $payroll = Payroll::query()->with('employee', 'itemAllowances', 'itemDeductions', 'benefits')->findOrFail('id', $id);

        $pdf = Pdf::loadView('pdf.payroll', compact('payroll'));
        return $pdf->stream('pdf.payroll');
    }

    public function jornal($transactions, Request $request)
    {
        $company = auth()->user()->employee->company;
        $transactions = Transaction::query()->whereIn('id', explode('-', $transactions))->get();
        // dd($transactions);

        $pdf = Pdf::loadView('pdf.jornal', compact('transactions', 'company'));
        return $pdf->stream('pdf.jornal');
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
        return $pdf->stream('pdf.account');
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
            ->pluck(null, 'name')
            ->map(function ($group) use ($request) {
                return
                [$group->name=>[
                    'sum'=>
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
                            })->sum()
                    ,
                    'item' => $group->childerns->pluck(null, 'name')->map(function ($child) use ($request) {
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
                        'item' => $child->childerns->pluck(null, 'name')->map(function ($item) use ($request) {
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
                })]];
            });

        // dd($accounts);


        $pdf = Pdf::loadView(
            'pdf.balance',
            compact('accounts', 'company', 'endDate')
        );
        return $pdf->stream('pdf.balance');
    }

    public function document($document)
    {
        $company = auth()->user()->employee->company;
        $document = Invoice::find($document);
        $pdf = Pdf::loadView(
            'pdf.document',
            compact('document', 'company')
        );
        return $pdf->stream('pdf.document');
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
        return $pdf->stream('pdf.trialBalance');
    }

    public function employee($id)
    {
        $employee =  Employee::query()->findOrFail('id', $id);
        $pdf = Pdf::loadView(
            'pdf.employee',
            compact('employee')
        );
        return $pdf->stream('pdf.employee');
    }
    public function payrolls($ids)
    {

        $payrolls =  Payroll::query()->whereIn('id', explode('-', $ids))->get();
        $company =  Company::query()->firstWhere('id', $payrolls[0]->company_id);
        $pdf = Pdf::loadView(
            'pdf.payrolls',
            compact('payrolls', 'company')
        );
        return $pdf->stream('pdf.payrolls');
    }

    public function purchase($id)
    {
        $pr=PurchaseRequest::query()->with(['company','items'])->findOrFail($id);
        $company=$pr->company;

        $pdf = Pdf::loadView(
            'pdf.purchase',
            compact('company','pr')
        );
        return $pdf->stream('pdf.purchase');
    }
    public function quotation($id)
    {
        $pr=PurchaseRequest::query()->with(['company','items'])->findOrFail($id);
        $company=$pr->company;


        $pdf = Pdf::loadView(
            'pdf.quotation',
            compact('company','pr')
        );
        return $pdf->stream('pdf.quotation');
    }
    public function bid($id)
    {
        $bid=Bid::query()->with(['company'])->findOrFail($id);
        $company=$bid->company;
        $PR=$bid->purchaseRequest;


        $pdf = Pdf::loadView(
            'pdf.bid',
            compact('company','bid','PR')
        );
        return $pdf->stream('pdf.bid');
    }
    public function separation($id)
    {
        $employee=Employee::query()->findOrFail($id);
        $company=$employee->company;


        $pdf = Pdf::loadView(
            'pdf.separation',
            compact('company','employee')
        );
        return $pdf->stream('pdf.separation');
    }
    public function takeOut($id)
    {
                $takeOut=TakeOut::query()->findOrFail($id);
        $company=$takeOut->company;


        $pdf = Pdf::loadView(
            'pdf.takeOut',
            compact('company','takeOut')
        );
        return $pdf->stream('pdf.takeOut');
    }
}
