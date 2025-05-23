
@include('pdf.header',
   ['titles'=>[''],'title'=>'Payroll Report/'.now()->format('F-Y'),'css'=>true])
<style>
    table th ,table td{
        font-size: 12px!important;
    }
</style>
<div class="table-container" >

    <table>
        <thead  >
        <tr >
            <th>#</th>
            <th>Employee</th>
            <th>Month</th>
            <th>Year</th>
            <th>Base Salary (Mount)</th>
            <th>Total Allowance</th>
            <th>Total Deduction</th>
            <th>Net Pay</th>
            <th>Status</th>
        </tr>
        </thead>
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
            <td >{{$i++}}</td>
            <td >{{$payroll->employee->fullName}}</td>
            <td>{{$month->format('M')}}</td>
            <td>{{$year}}</td>
            <td>{{number_format($payroll->employee?->base_salary).' '.$payroll->employee?->currency?->name}}</td>
            <td>{{number_format($payroll->total_allowance)}}</td>
            <td>{{number_format($payroll->total_deduction)}}</td>
            <td>{{number_format($payroll->amount_pay)}}</td>
            <td>{{$payroll->status->name}}</td>
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

