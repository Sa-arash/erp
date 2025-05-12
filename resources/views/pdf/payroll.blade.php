
@php
    $month = \Carbon\Carbon::parse($payroll->start_date);
    $year = \Carbon\Carbon::parse($payroll->start_date)->year;
    $leaveType=\App\Models\Typeleave::query()->where('company_id',$payroll->company_id)->where('built_in',1)->first();
    $annualLeaves= \App\Models\Leave::query()->where('status','accepted')->whereBetween('start_leave',[now()->startOfYear(),now()->endOfYear()])->whereBetween('end_leave',[now()->startOfYear(),now()->endOfYear()])->where('typeleave_id',$leaveType?->id)->where('employee_id',$payroll->employee_id)->get();
    $leaves= \App\Models\Leave::query()->where('status','accepted')->whereBetween('start_leave',[now()->startOfMonth(),now()->endOfMonth()])->whereBetween('end_leave',[now()->startOfMonth(),now()->endOfMonth()])->where('employee_id',$payroll->employee_id)->get();
@endphp


@include('pdf.header',['title'=>'Payroll-'.$month->format('M')." ".$year,'titles'=>[],'css'=>false])



    <style>
        @page {
            margin-top: 10px;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff !important;
            margin: 0;
            padding: 0;
            word-spacing: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-size: 12px;

        }

        .pay-slip {
            background: #fff;
            padding: 20px;
        }
        .text-center{
            alignment: center;
        }
        .w-100{
            width: 100%;
        }

        .pay-slip h1 {
            font-size: 15px;
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }

        .pay-slip h2 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #555;
            text-align: center;
        }

        .pay-slip .section {
            margin-bottom: 10px;
        }

        .pay-slip .section h3 {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
            padding-bottom: 5px;
        }

        .pay-slip table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .pay-slip table th, .pay-slip table td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .pay-slip table th {
            background: #f7f7f7;
            font-weight: bold;
        }

        .pay-slip .total {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            color: #333;
        }
        @page  {
           margin: 0px;
        }
    </style>
</head>
<body>
@php
    $totalAllowances=0;
    $totalDeductions=0;
@endphp
<div class="pay-slip">

    <div style="width: 100%;display: flex">
            @if($payroll->employee->media->where('collection_name','images')->first()?->original_url )
                <div >            <img   src="{!! $payroll->employee->media->where('collection_name','images')->first()?->original_url!!}" style="width: 95px;margin-bottom: 20px;margin-left: 42%;border: 1px solid gray;padding: 5px">
                </div>
            @endif
    </div>
    <table class="details">
        <tr>
            <th colspan="4" style="text-align: center;font-size: 20px"><b> Employee Information</b></th>
        </tr>
        <tr>
            <th>Employee ID</th>
            <td>{{ $payroll->employee->ID_number }}</td>
            <th>Name</th>
            <td>{{ $payroll->employee->fullName }}</td>

        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $payroll->employee->department->title }}</td>
            <th>Designation</th>
            <td>{{ $payroll->employee->position->title }}</td>

        </tr>
        <tr>
            <th> Working Days</th>
            <td colspan="1">
                {{$month->daysInMonth-$annualLeaves->sum('days')}} Of {{$month->daysInMonth}}
            </td>
            <th>Annual Leaves</th>
            <td colspan="1">
                {{$annualLeaves->sum('days')}} Of {{$leaveType?->days}} --- Remining Leaves {{$leaveType?->days- $annualLeaves->sum('days') }} Days
            </td>
        </tr>
    </table>

    <div class="section">
        <h3>Salary And Additions</h3>
        <table>
            <tr>
                <th>Description</th>
                <th>Amount (({{ PDFdefaultCurrency($payroll->company)}}))</th>
            </tr>
            <tr>
                <td>Daily Salary</td>
                <td>{{ number_format($payroll->employee?->daily_salary) }}</td>
            </tr>
            <tr>
                <td>Base Salary</td>
                <td>   {{ number_format($payroll->employee?->base_salary) }}</td>
            </tr>

            @foreach($payroll->benefits->where('type',"allowance")->sortBy('built_in')  as $allowance)
                <tr>
                    <td>{{$allowance->title}}</td>
                    <td>{{$allowance->pivot->amount >0? number_format( $allowance->pivot->amount):$allowance->pivot->percent."%"." (".$allowance->on_change.")" }}</td>
                </tr>
            @endforeach

            <tr class="">
                <td style="font-size: 18px">Total Earnings</td>
                <td style="color: #1cc6b9;font-size: 15px" >{{ number_format($payroll->employee?->base_salary+$payroll->total_allowance ) .' '.PDFdefaultCurrency($payroll->company) }}</td>
            </tr>

        </table>
    </div>

    <div class="section">
        <h3>Deductions</h3>
        <table>
            <tr>
                <th>Description</th>
                <th>Amount (({{ PDFdefaultCurrency($payroll->company)}}))</th>
            </tr>
            @foreach($payroll->benefits->where('type',"deduction")->sortBy('built_in')  as $deduction)
            <tr>
                <td>{{$deduction->title }}</td>
                <td>{{$deduction->pivot->amount >0? number_format( $deduction->pivot->amount):$deduction->pivot->percent."%"." (".$deduction->on_change.")" }}</td>
            </tr>
            @endforeach
            <tr>
                <td >Total Deductions</td>
                <td style="color: #1cc6b9;font-size: 15px">{{number_format($payroll->total_deduction    )}}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Net Pay</h3>
        <table>
            <tr>
                <td>Total Earnings</td>
                <td>{{number_format($payroll->employee?->base_salary+$payroll->total_allowance)}}</td>
            </tr>
            <tr>
                <td>Total Deductions</td>
                <td>{{number_format($payroll->total_deduction)}}</td>
            </tr>
            <tr>
                <td >Net Pay </td>
                <td   style="color: #1cc6b9;font-size: 15px"  >{{ number_format($payroll->amount_pay ).' '.PDFdefaultCurrency($payroll->company)}}</td>
            </tr>
        </table>
    </div>
    @if($payroll->employee->media->where('collection_name','signature')->first()?->original_url )
       <pre style="margin-left: 40%!important;">                                         Employee Signature</pre>
    <div class="text-center  w-100" style="margin-left: 300px">

           <img  src="{!! $payroll->employee->media->where('collection_name','signature')->first()?->original_url!!}" style="width: 95px;margin-bottom: 20px">
   </div>
    @endif
</div>

</body>
</html>
