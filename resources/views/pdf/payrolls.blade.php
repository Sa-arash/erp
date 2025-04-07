
@include('pdf.header',
   ['titles'=>['Payroll Report'],'title'=>'Employee Salary '])
<div class="table-container">

    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Month</th>
            <th>Year</th>
            <th>Base Salary</th>
            <th>Total Allowance</th>
            <th>Total Deduction</th>
            <th>Net Pay</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalBase=0;
        @endphp
        @foreach($payrolls as $payroll)
            @php
            $totalBase+=$payroll->employee?->base_salary;
                $month = \Carbon\Carbon::parse($payroll->start_date);
                $year = \Carbon\Carbon::parse($payroll->start_date)->year;
            @endphp
        <tr>
            <td>{{$payroll->employee->fullName}}</td>
            <td>{{$month->format('M')}}</td>
            <td>{{$year}}</td>
            <td>{{number_format($payroll->employee?->base_salary)}}</td>
            <td>{{number_format($payroll->total_allowance)}}</td>
            <td>{{number_format($payroll->total_deduction)}}</td>
            <td>{{number_format($payroll->amount_pay)}}</td>
            <td>{{$payroll->status->name}}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr >
            <th style="background:#4b4949!important;" colspan="3">Total </th>
            <td style="background:#4b4949!important;color: white">{{number_format($totalBase)}}</td>
            <th style="background:#4b4949!important;" >{{number_format($payrolls->sum('total_allowance'))}}</th>
            <td style="background:#4b4949!important;color: white">{{number_format($payrolls->sum('total_deduction'))}}</td>
            <th style="background:#4b4949!important;">{{number_format($payrolls->sum('amount_pay'))}}</th>
            <td style="background:#4b4949!important;"></td>
        </tr>

        </tfoot>
    </table>
</div>

