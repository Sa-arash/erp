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
                    <table class="w-full divide-y divide-gray-200 str">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Month
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Total Salaries Paid
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Total Expenses
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Total Revenue
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Number of PR
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Total PO
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Total Accounts Payable
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">
                                    Total Accounts Receivable
                                </th>
                               
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($fillerArray as $key => $mount)
                                {{-- @dd($fillerArray, $monthArray, $monthArray[$key][0]); --}}
                                @php
                                    $totalPaid = getCompany()
                                        ->payrolls->where('start_date', '>=', $monthArray[$key][0])
                                        ->where('end_date', '<=', $monthArray[$key][1])
                                        ->sum('amount_pay');
                                    $totalSalariesPaid += $totalPaid;

                                    $countPR = getCompany()
                                        ->purchaseRequests->whereBetween('request_date', [
                                            $monthArray[$key][0],
                                            $monthArray[$key][1],
                                        ])
                                        ->count();
                                    $totalPR += $countPR;
                                    $sumPO = App\Models\PurchaseOrder::where('company_id', getCompany()->id)
                                        ->whereBetween('date_of_po', [$monthArray[$key][0], $monthArray[$key][1]])
                                        ->with('items')
                                        ->get()
                                        ->flatMap(fn($po) => $po->items)
                                        ->map(
                                            fn($item) => $item['quantity'] * str_replace(',', '', $item['unit_price']) +
                                                ($item['quantity'] *
                                                    str_replace(',', '', $item['unit_price']) *
                                                    $item['taxes']) /
                                                    100 +
                                                ($item['quantity'] *
                                                    str_replace(',', '', $item['unit_price']) *
                                                    $item['freights']) /
                                                    100,
                                        )
                                        ->sum();
                                    $totalPO += $sumPO;
                                    $incomeData = collect(range(0, count($fillerArray) - 1)) // ایجاد کالکشن از تعداد آیتم‌های fillerArray
                                        ->map(function ($index) use ($monthArray) {
                                            return getCompany()
                                                ->accounts->where('group', 'Income')
                                                ->flatMap(fn($account) => $account->transactions)
                                                ->whereBetween('created_at', [
                                                    $monthArray[$index][0],
                                                    $monthArray[$index][1],
                                                ])
                                                ->sum(
                                                    fn($transaction) => $transaction->creditor - $transaction->debtor,
                                                );
                                        })
                                        ->toarray();

                                    $expenseData = collect(range(0, count($fillerArray) - 1)) // مانند بالا برای هزینه‌ها
                                        ->map(function ($index) use ($monthArray) {
                                            return getCompany()
                                                ->accounts->where('group', 'Expense')
                                                ->flatMap(fn($account) => $account->transactions)
                                                ->whereBetween('created_at', [
                                                    $monthArray[$index][0],
                                                    $monthArray[$index][1],
                                                ])
                                                ->sum(
                                                    fn($transaction) => $transaction->debtor - $transaction->creditor,
                                                );
                                        })
                                        ->toarray();

                                    $RecivableData = collect(range(0, count($fillerArray) - 1))
                                        ->map(function ($index) use ($monthArray) {
                                            // دریافت حساب‌های Receivable
                                            $receivableAccounts = getCompany()->accounts->where(
                                                'stamp',
                                                'Accounts Receivable',
                                            );

                                            // دریافت ID همه‌ی حساب‌های مرتبط
                                            $receivableIds = $receivableAccounts->pluck('id')->toArray();

                                            // پیدا کردن حساب‌های زیرمجموعه
                                            $allReceivableIds = collect($receivableIds);
                                            $newIds = $receivableIds;

                                            do {
                                                $subAccounts = getCompany()
                                                    ->accounts->whereIn('parent_id', $newIds)
                                                    ->pluck('id')
                                                    ->toArray();

                                                $newIds = array_diff($subAccounts, $allReceivableIds->toArray());
                                                $allReceivableIds = $allReceivableIds->merge($newIds);
                                            } while (!empty($newIds));

                                            // دریافت تراکنش‌ها برای تمام حساب‌های مرتبط
                                            return getCompany()
                                                ->accounts->whereIn('id', $allReceivableIds)
                                                ->flatMap(fn($account) => $account->transactions)
                                                ->whereBetween('created_at', [
                                                    $monthArray[$index][0],
                                                    $monthArray[$index][1],
                                                ])
                                                ->sum(
                                                    fn($transaction) => $transaction->creditor - $transaction->debtor,
                                                );
                                        })
                                        ->toArray();


                                    $PayableData = collect(range(0, count($fillerArray) - 1))
                                        ->map(function ($index) use ($monthArray) {
                                            // دریافت حساب‌های Payable
                                            $payableAccounts = getCompany()->accounts->where(
                                                'stamp',
                                                'Accounts Payable',
                                            );

                                            // دریافت ID همه‌ی حساب‌های مرتبط
                                            $payableIds = $payableAccounts->pluck('id')->toArray();

                                            // پیدا کردن حساب‌های زیرمجموعه
                                            $allPayableIds = collect($payableIds);
                                            $newIds = $payableIds;

                                            do {
                                                $subAccounts = getCompany()
                                                    ->accounts->whereIn('parent_id', $newIds)
                                                    ->pluck('id')
                                                    ->toArray();

                                                $newIds = array_diff($subAccounts, $allPayableIds->toArray());
                                                $allPayableIds = $allPayableIds->merge($newIds);
                                            } while (!empty($newIds));

                                            // دریافت تراکنش‌ها برای تمام حساب‌های مرتبط
                                            return getCompany()
                                                ->accounts->whereIn('id', $allPayableIds)
                                                ->flatMap(fn($account) => $account->transactions)
                                                ->whereBetween('created_at', [
                                                    $monthArray[$index][0],
                                                    $monthArray[$index][1],
                                                ])
                                                ->sum(
                                                    fn($transaction) => $transaction->creditor - $transaction->debtor,
                                                );
                                        })
                                        ->toArray();

                                    // dd($PayableData);

                                    // $receivable_accounts = App\Models\Account::query()
                                    //     ->whereIn('id', $receivable_id)
                                    //     ->orWhereIn('parent_id', $receivable_id)
                                    //     ->orWhereHas('account', function ($query) use ($receivable_id) {
                                    //         return $query
                                    //             ->whereIn('parent_id', $receivable_id)
                                    //             ->orWhereHas('account', function ($query) use ($receivable_id) {
                                    //                 return $query->whereIn('parent_id', $receivable_id);
                                    //             });
                                    //     })
                                    //     ->get();

                                    //     $sumRecivable = $receivable_accounts->map(fn($account)=>$account->transactions->sum(fn($transaction) =>  $transaction->creditor - $transaction->debtor))->sum();

                                    //     $sumPayable = $payable_accounts->map(fn($account)=>$account->transactions->sum(fn($transaction) =>  $transaction->creditor - $transaction->debtor))->sum();

                                    // $courseCount=\App\Models\Course::query()->where('price','!=',0)->where('pre_registration',0)->whereBetween('start_date',[$mountArray[$key][0],$mountArray[$key][1]])->count();
                                    // $register=\App\Models\Register::query()->whereHas('course',function ($query){return  $query->where('price','!=',0)->where('pre_registration',0);})->whereBetween('register_date',[$mountArray[$key][0],$mountArray[$key][1]])->count();
                                    // $courseMinFive=$courseMinGet->count();
                                    // $totalMaxCourse=$courseMaxGet->count();
                                    // $payment=\App\Models\Payment::query()->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('payment');
                                    // $countPay=\App\Models\Payment::query()->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->count();
                                    // $remaining=\App\Models\Register::query()->whereHas('course',function ($query){return  $query->where('price','!=',0)->where('pre_registration',0);})->whereBetween('register_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('remaining_amount');
                                    // $courses= \App\Models\Course::query()->where('price','!=',0)->where('pre_registration',0)->withSum('salaryTeacher','price_to_pay')->whereBetween('start_date',[$mountArray[$key][0],$mountArray[$key][1]])->get('salary_teacher_sum_price_to_pay');
                                    // $total=\App\Models\Expense::query()->where('category_id',20)->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('price');
                                    // $otherExpense=\App\Models\Expense::query()->whereNotIn('category_id',[18,19,20,29,30,43,52])->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('price');
                                    // $expensePayroll=\App\Models\Expense::query()->whereIn('category_id',[18,19,43,52])->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('price');
                                    // $expenses=\App\Models\Expense::query()->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('price');
                                    // $expenseCourse=\App\Models\Expense::query()->whereIn('category_id',[29,30])->whereBetween('payment_date',[$mountArray[$key][0],$mountArray[$key][1]])->sum('price');
                                    // $users=\App\Models\User::query()->whereBetween('created_at',[$mountArray[$key][0],$mountArray[$key][1]])->count();

                                    //               $i=0;
                                    //             $arrayCourse2=[];
                                    //             foreach ($courses as $course){
                                    //                 $arrayCourse2['tableFilters[course_id][values]['.$i.']']=$course->id;
                                    //                $i++;
                                    //             }
                                    //      $totalOtherExpense+=$otherExpense;
                                    //      $totalPayroll+=$expensePayroll;
                                    //      $totalExpenseCourse+=$expenseCourse;
                                    //      $totalCourse+=$courseCount;
                                    //      $totalRegister+=$register;
                                    //      $totalCourseMinFive+=$courseMinFive;
                                    //      $totalCourseMaxTen+=$totalMaxCourse;
                                    //      $totalPay+=$payment;
                                    //      $totalCountPay+=$countPay;
                                    //      $totalRemaining+=$remaining;
                                    //      $totalTeacher+=$total;
                                    //      $totalExpense+=$expenses;
                                    //      $arrayCourse=[];
                                    //      $keyGet=0;
                                    //      foreach ($courseMinGet as $minGet){
                                    //          $arrayCourse['tableFilters[id][values]['.$keyGet.']']=$minGet->id;
                                    //          $keyGet+=1;
                                    //      }
                                    //      $arrayCourse1=[];
                                    //      $keyGet=0;
                                    //      foreach ($courseMaxGet as $maxGet){
                                    //          $arrayCourse1['tableFilters[id][values]['.$keyGet.']']=$maxGet->id;
                                    //          $keyGet+=1;
                                    //      }
                                    //      $totalUser+=$users;

                                @endphp
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        {{ $mount }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\PayrollResource::getUrl('index') }}">{{ number_format($totalPaid) }}</a>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\AccountResource::getUrl('index') }}">{{ number_format($incomeData[$key]) }}</a>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\AccountResource::getUrl('index') }}">{{ number_format($expenseData[$key]) }}</a>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\AccountResource::getUrl('index') }}">{{ number_format($countPR) }}</a>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\AccountResource::getUrl('index') }}">{{ number_format($sumPO) }}</a>
                                    </td> 
                                     <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\AccountResource::getUrl('index') }}">{{ number_format($RecivableData[$key]) }}</a>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                        <a
                                            href="{{ App\Filament\Admin\Resources\AccountResource::getUrl('index') }}">{{ number_format($PayableData[$key]) }}</a>
                                    </td>
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\CourseResource::getUrl('index', $arrayCourse)}}">{{$courseMinFive}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\CourseResource::getUrl('index', $arrayCourse1)}}">{{$totalMaxCourse}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\RegisterResource::getUrl('index',['tableFilters[is_preregister][value]'=>0,'tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{$register}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\UserResource::getUrl('index',['tableFilters[dates][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[dates][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{$users}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center"
                                    style="background: #b5d3cc;"><a
                                        href="{{\App\Filament\Resources\PaymentResource::getUrl('index',['tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{number_format($payment)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\PaymentResource::getUrl('index',['tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{$countPay}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\RegisterResource::getUrl('index',['tableFilters[is_preregister][value]'=>0,'tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString(),'tableFilters[data][priceStart]'=>100])}}">{{number_format($remaining)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\ExpenseResource::getUrl('index',['tableFilters[category_id][value]'=>20,'tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{number_format($total)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\ExpenseResource::getUrl('index',['tableFilters[category_id][value]'=>14,'tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{number_format($expensePayroll)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\ExpenseResource::getUrl('index',['tableFilters[category_id][value]'=>28,'tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{number_format($expenseCourse)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    <a href="{{\App\Filament\Resources\ExpenseResource::getUrl('index',['tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{number_format($otherExpense)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center"
                                    style="background: #b5d3cc;"><a
                                        href="{{\App\Filament\Resources\ExpenseResource::getUrl('index',['tableFilters[data][startDate]'=>$mountArray[$key][0]->toDateString(),'tableFilters[data][endDate]'=>$mountArray[$key][1]->toDateString()])}}">{{number_format($expenses)}}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">{{number_format($payment-($expenses))}}</td> --}}
                                </tr>
                            @endforeach
                        <tfoot>
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    Total
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    {{ number_format($totalSalariesPaid) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    {{ number_format(collect($incomeData)->sum()) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    {{ number_format(collect($expenseData)->sum()) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    {{ number_format($totalPR) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                    {{ number_format($totalPO) }}
                                </td>
                                <td
                                class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                {{ number_format(collect($RecivableData)->sum()) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm items-center font-medium text-gray-800 text-center">
                                {{ number_format(collect($PayableData)->sum()) }}
                            </td>
                             
                        
                            </tr>
                        </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
