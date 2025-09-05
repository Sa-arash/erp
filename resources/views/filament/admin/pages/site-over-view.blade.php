<x-filament-panels::page>
    {{ $this->filtersForm }}

    @php
        $fillerArray = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        $monthNames = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        if (isset($this->filters['year'])) {
            $year = $this->filters['year'];
            // dd($this->filters['year']);
            $monthArray = [];

            for ($i = 1; $i <= 12; $i++) {
                $monthArray[] = [
                    \Illuminate\Support\Carbon::create($year, $i, 1)->startOfMonth(),
                    \Illuminate\Support\Carbon::create($year, $i, 1)->endOfMonth(),
                ];
            }
        } else {
            $monthArray = [];

            for ($i = 1; $i <= 12; $i++) {
                $monthArray[] = [
                    \Illuminate\Support\Carbon::now()->month($i)->startOfMonth(),
                    \Illuminate\Support\Carbon::now()->month($i)->endOfMonth(),
                ];
            }
        }
        $totalSalariesPaid = 0;
        $totalExpenses = 0;
        $totalRevenue = 0;
        $totalPR = 0;
        $totalPO = 0;
    @endphp


    {{--    <style> --}}
    {{--        tr:nth-child(even) { --}}
    {{--            background-color: #737a5c; --}}
    {{--        }</style> --}}
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto border rounded-lg">
            <div class="p-1.5 w-full inline-block align-middle">
                <div class=" shadow overflow-x-scroll table-responsive">
                    @php
                        $totalSalariesPaid = 0;
                        $totalPR = 0;
                        $totalPO = 0;

                        // محاسبه درآمد
                        $incomeData = collect($monthArray)->map(function ($range) {
                            return getCompany()
                                ->accounts->where('group', 'Income')
                                ->flatMap(fn($account) => $account->transactions)
                                ->whereBetween('created_at', [$range[0], $range[1]])
                                ->sum(fn($transaction) => $transaction->creditor - $transaction->debtor);
                        })->toArray();

                        // محاسبه هزینه‌ها
                        $expenseData = collect($monthArray)->map(function ($range) {
                            return getCompany()
                                ->accounts->where('group', 'Expense')
                                ->flatMap(fn($account) => $account->transactions)
                                ->whereBetween('created_at', [$range[0], $range[1]])
                                ->sum(fn($transaction) => $transaction->debtor - $transaction->creditor);
                        })->toArray();

                        // محاسبه دریافتنی‌ها
                        $RecivableData = collect($monthArray)->map(function ($range) {
                            $receivableAccounts = getCompany()->accounts()->where('stamp', 'Accounts Receivable');
                            $receivableIds = $receivableAccounts->pluck('id')->toArray();

                            $allReceivableIds = collect($receivableIds);
                            $newIds = $receivableIds;
                            do {
                                $subAccounts = getCompany()->accounts()->whereIn('parent_id', $newIds)->pluck('id')->toArray();
                                $newIds = array_diff($subAccounts, $allReceivableIds->toArray());
                                $allReceivableIds = $allReceivableIds->merge($newIds);
                            } while (!empty($newIds));

                            return getCompany()
                                ->accounts->whereIn('id', $allReceivableIds)
                                ->flatMap(fn($account) => $account->transactions)
                                ->whereBetween('created_at', [$range[0], $range[1]])
                                ->sum(fn($transaction) => $transaction->creditor - $transaction->debtor);
                        })->toArray();

                        // محاسبه پرداختنی‌ها
                        $PayableData = collect($monthArray)->map(function ($range) {
                            $payableAccounts = getCompany()->accounts->where('stamp', 'Accounts Payable');
                            $payableIds = $payableAccounts->pluck('id')->toArray();

                            $allPayableIds = collect($payableIds);
                            $newIds = $payableIds;
                            do {
                                $subAccounts = getCompany()->accounts->whereIn('parent_id', $newIds)->pluck('id')->toArray();
                                $newIds = array_diff($subAccounts, $allPayableIds->toArray());
                                $allPayableIds = $allPayableIds->merge($newIds);
                            } while (!empty($newIds));

                            return getCompany()
                                ->accounts->whereIn('id', $allPayableIds)
                                ->flatMap(fn($account) => $account->transactions)
                                ->whereBetween('created_at', [$range[0], $range[1]])
                                ->sum(fn($transaction) => $transaction->creditor - $transaction->debtor);
                        })->toArray();
                    @endphp

                    <table class="w-full divide-y divide-gray-200 str">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Month</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Total Salaries Paid</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Total Expenses</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Total Revenue</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Number of PR</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Total PO</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Total Accounts Receivable</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Total Accounts Payable</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach ($fillerArray as $key => $mount)
                            @php
                                $totalPaid = getCompany()
                                    ->payrolls()
                                    ->where('start_date', '>=', $monthArray[$key][0])
                                    ->where('end_date', '<=', $monthArray[$key][1])
                                    ->sum('amount_pay');
                                $totalSalariesPaid += $totalPaid;

                                $countPR = getCompany()
                                    ->purchaseRequests()
                                    ->whereBetween('request_date', $monthArray[$key])
                                    ->count();
                                $totalPR += $countPR;

                                $sumPO = App\Models\PurchaseOrder::query()
                                    ->where('company_id', getCompany()->id)
                                    ->whereBetween('date_of_po', $monthArray[$key])
                                    ->withSum('items', 'total')
                                    ->get()
                                    ->sum('items_sum_total');
                                $totalPO += $sumPO;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ $mount }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($totalPaid) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($expenseData[$key]) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($incomeData[$key]) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($countPR) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($sumPO) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($RecivableData[$key]) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($PayableData[$key]) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">Total</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($totalSalariesPaid) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format(collect($expenseData)->sum()) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format(collect($incomeData)->sum()) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($totalPR) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format($totalPO) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format(collect($RecivableData)->sum()) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 text-center">{{ number_format(collect($PayableData)->sum()) }}</td>
                        </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
