
@include('pdf.header',
   ['titles'=>[],'title'=>'Payroll Report','css'=>true])
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-size: 12px;
        /* کوچک‌تر کردن فونت جدول */
    }
    table {
        page-break-inside: auto;
    }
    th,
    td {
        padding: 10px;
        /* کاهش اندازه padding برای تراکم بیشتر محتوا */
        text-align: left;
        font-size: 12px;
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
        margin: 10px;
    }
</style>
<div class="table-container" >

    <table>

        <tr >
            <th>#</th>
            <th>Employee</th>
            <th>Month</th>
            <th>Year</th>
            <th>Base Salary (Amount)</th>
            <th>Total Allowance</th>
            <th>Total Deduction</th>
            <th colspan="2">Net Pay</th>
            <th >Status</th>
        </tr>


        <tbody>
        @php
            $totalBase=0;
            $i=1;
            $totals=[];
        @endphp
        @foreach($payrolls as $payroll)
            @php
                if (key_exists($payroll->employee->currency?->name,$totals)){
                    $totals[$payroll->employee->currency?->name]= $totals[$payroll->employee->currency?->name] +$payroll->amount_pay;
                }else{
                 $totals[$payroll->employee->currency?->name]=$payroll->amount_pay;
                }

                $totalBase+=$payroll->employee?->base_salary;
                    $month = \Carbon\Carbon::parse($payroll->start_date);
                    $year = \Carbon\Carbon::parse($payroll->start_date)->year;
            @endphp
        <tr style="margin:  0!important;text-align: center!important;" >
            <td  style="padding: 5px">{{$i++}}</td>
            <td  style="padding: 5px">{{$payroll->employee->fullName}}</td>
            <td style="padding: 5px">{{$month->format('F')}}</td>
            <td style="padding: 5px">{{$year}}</td>
            <td style="padding: 5px">{{number_format($payroll->employee?->base_salary).' '.$payroll->employee?->currency?->name}}</td>
            <td style="padding: 5px">{{number_format($payroll->total_allowance).' '.$payroll->employee?->currency?->name}}</td>
            <td style="padding: 5px">{{number_format($payroll->total_deduction).' '.$payroll->employee?->currency?->name}}</td>
            <td style="padding: 5px" colspan="2">{{number_format($payroll->amount_pay).' '.$payroll->employee?->currency?->name}}</td>
            <td  style="padding: 5px">{{$payroll->status->name}}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr >
{{--            <th style="background:#4b4949!important;" colspan="4">Total </th>--}}
{{--            <td style="background:#4b4949!important;color: white">{{number_format($totalBase)}}</td>--}}
{{--            <th style="background:#4b4949!important;" >{{number_format($payrolls->sum('total_allowance'))}}</th>--}}
{{--            <td style="background:#4b4949!important;color: white">{{number_format($payrolls->sum('total_deduction'))}}</td>--}}
{{--            <th style="background:#4b4949!important;">{{number_format($payrolls->sum('amount_pay'))}}</th>--}}
{{--            <td style="background:#4b4949!important;"></td>--}}

        </tr>
        </tfoot>
    </table>
    <table>
        @foreach($totals as $key=> $totalPay)
            <tr>

                <td><b>Total  {{$key}}</b> : {{number_format($totalPay,2)}}</td>
            </tr>
        @endforeach

    </table>


        <span style="font-size: 12px;color: gray; margin-left:40% !important; margin-top: auto ">Print Date: {{now()->format('Y/F/d H:iA')}}</span>

</div>

