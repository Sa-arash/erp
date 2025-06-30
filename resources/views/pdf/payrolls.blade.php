
@include('pdf.header',
   ['titles'=>[],'title'=>'Payroll Report','css'=>true,'dateShow'=>true])
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-size: 10px;
        /* کوچک‌تر کردن فونت جدول */
    }
    table {
        page-break-inside: auto;
    }
    th,
    td {
        padding: 3px;
        /* کاهش اندازه padding برای تراکم بیشتر محتوا */
        text-align: left;
        font-size: 10px;
        /* کوچک‌تر کردن فونت سلول‌ها */
    }


    table th ,table td{
        font-size: 12px!important;
    }

    th,
    td {
        border: 1px solid #000;
        font-size: 10pt;
    }
    @page  {
        margin-top: 28mm;
        margin-left: 25px;
        margin-right:25px ;
    }

</style>
<div class="table-container">
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
        <tr style="text-align: center;">
            <th>#</th>
            <th>Employee</th>
            <th>Department</th>
            <th>Month</th>
            <th>Year</th>
            <th>Base Salary</th>
            <th>Total Allowance</th>
            <th>Total Deduction</th>
            <th colspan="2">Net Pay</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totals = [];
            $i = 1;
        @endphp

        @foreach($payrolls as $department => $records)
            {{-- سطر گروه (دپارتمان) --}}
{{--            <tr style="background-color: #f1f1f1; text-align: left;">--}}
{{--                <td colspan="11" style="font-weight: bold; padding: 6px;">--}}
{{--                    Department: {{ $department }}--}}
{{--                </td>--}}
{{--            </tr>--}}

            @foreach($records as $payroll)
                @php
                    $currency = $payroll->employee->currency?->name;
                    $symbol = $payroll->employee->currency?->symbol;
                    $month = \Carbon\Carbon::parse($payroll->start_date)->format('F');
                    $year = \Carbon\Carbon::parse($payroll->start_date)->year;
                    $totals[$currency] = ($totals[$currency] ?? 0) + $payroll->amount_pay;
                @endphp
                <tr style="text-align: center;">
                    <td>{{ $i++ }}</td>
                    <td>{{ $payroll->employee->fullName }}</td>
                    <td>{{ $payroll->employee->department?->title }}</td>
                    <td>{{ $month }}</td>
                    <td>{{ $year }}</td>
                    <td>{{ number_format($payroll->employee->base_salary) . ' ' . $currency }}</td>
                    <td>{{ number_format($payroll->total_allowance) . ' ' . $currency }}</td>
                    <td>{{ number_format($payroll->total_deduction) . ' ' . $currency }}</td>
                    <td colspan="2">{{ number_format($payroll->amount_pay) . ' ' . $currency }}</td>
                    <td>{{ $payroll->status->name }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

    {{-- مجموع کل در انتها --}}
    <table style="margin-top: 20px;" border="1">
        @foreach($totals as $currency => $total)
            <tr>
                <td><strong>Total {{ $currency }}</strong>: {{ number_format($total, 2) }}</td>
            </tr>
        @endforeach
    </table>
</div>



