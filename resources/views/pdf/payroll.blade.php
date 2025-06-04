@php
    $month = \Carbon\Carbon::parse($payroll->start_date);
    $year = \Carbon\Carbon::parse($payroll->start_date)->year;
    $leaveType = \App\Models\Typeleave::query()
        ->where('company_id', $payroll->company_id)
        ->where('built_in', 1)
        ->first();
    $annualLeaves = \App\Models\Leave::query()
        ->where('status', 'accepted')
        ->whereBetween('start_leave', [now()->startOfYear(), now()->endOfYear()])
        ->whereBetween('end_leave', [now()->startOfYear(), now()->endOfYear()])
        ->where('typeleave_id', $leaveType?->id)
        ->where('employee_id', $payroll->employee_id)
        ->get();
    $leaves = \App\Models\Leave::query()
        ->where('status', 'accepted')
        ->whereBetween('start_leave', [now()->startOfMonth(), now()->endOfMonth()])
        ->whereBetween('end_leave', [now()->startOfMonth(), now()->endOfMonth()])
        ->where('employee_id', $payroll->employee_id)
        ->get();
        $currency = $payroll->employee?->currency?->name;
@endphp


@include('pdf.header', [
    'title' => 'Payroll-' . $month->format('M') . ' ' . $year,
    'titles' => [],
    'css' => false,
])


{{--
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

    </style>

<body>
@php
    $totalAllowances=0;
    $totalDeductions=0;
@endphp
<div class="pay-slip">

    <div style="width: 100%;display: flex">
            @if ($payroll->employee->media->where('collection_name', 'images')->first()?->original_url)
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

            @foreach ($payroll->benefits->where('type', 'allowance')->sortBy('built_in') as $allowance)
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
            @foreach ($payroll->benefits->where('type', 'deduction')->sortBy('built_in') as $deduction)
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
    @if ($payroll->employee->media->where('collection_name', 'signature')->first()?->original_url)
       <pre style="margin-left: 40%!important;">                                         Employee Signature</pre>
    <div class="text-center  w-100" style="margin-left: 300px">

           <img  src="{!! $payroll->employee->media->where('collection_name','signature')->first()?->original_url!!}" style="width: 95px;margin-bottom: 20px">
   </div>
    @endif
</div>

</body>
</html> --}}


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    {{-- <title>Payroll Statement</title> --}}
    <style>
        body{
            font-size: 12px;
        }


.pay-table{
    width: 50%;
}
        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 8px;
        }

        .summary-row td {
            font-weight: bold;
            background-color: #e0e0e0;
            /* رنگ خاکستری ساده */
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .deductions {
            margin-top: 40px;
        }
        .bold{
            font-weight: bold;
        }

    </style>
</head>

<body>
    <br>

    <div class="clearfix">
        <div class="company-info">
            {{-- <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 50px;"><br> --}}
            AREA TARGET GENERAL TRADING L.L.C<br>
            AL MERAIKHI TOWER 2, RIG AL BUTIN RD,<br>
            DEIRA, DUBAI, UAE<br>
            Email: <a href="mailto:finance@unccompound.com">finance@unccompound.com</a><br>
            Contact: +971 56 152 7710
        </div>

        {{-- <div class="earnings-info">
            <div class="title">Earnings Statement</div>
            <strong>Period Beginning:</strong> <span class="highlight">04/01/2025</span><br>
            <strong>Period Ending:</strong> <span class="highlight">04/30/2025</span><br>
            <strong>Pay Date:</strong> <span class="highlight">05/01/2025</span>
        </div> --}}
    </div>
    <br>


    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
        <tr>
            <!-- Employee Information -->
            <td style="vertical-align: top; width: 70%; padding: 0; margin: 0;">
                <table style="border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Employee:</td>
                        <td><span style=" padding: 0 3px;">{{ $payroll->employee->fullName }}</span></td>
                    </tr>
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Employee#:</td>
                        <td><span style=" padding: 0 3px;">  {{ $payroll->employee->ID_number }}</span></td>
                    </tr>
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Department:</td>
                        <td><span style=" padding: 0 3px;">{{ $payroll->employee->department->title }}</span></td>
                    </tr>
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Address:</td>
                        <td>
                            <span style=" padding: 0 3px;">
                                {{ $payroll->employee->address }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Earnings Statement -->
            {{-- @dd($payroll) --}}
            <td style="vertical-align: top; width: 30%; padding: 0; margin: 0;">
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;" class="bold">Earnings Statement</div>
                <br>
                <table style="border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Period Beginning:</td>
                        <td><span style=" padding: 0 3px;">{{\Carbon\Carbon::parse($payroll->start_date)->format('d/m/Y')}}</span></td>
                    </tr>
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Period Ending:</td>
                        <td><span style=" padding: 0 3px;">{{\Carbon\Carbon::parse($payroll->end_date)->format('d/m/Y')}}</span></td>
                    </tr>
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;" class="bold">Pay Date:</td>
                        <td><span style=" padding: 0 3px;">{{\Carbon\Carbon::parse($payroll->pay_date)->format('d/m/Y')}}</span></td>
                    </tr>
                </table>
            </td>


        </tr>
    </table>
    <hr>
    <br>
    <br>




    {{-- <div class="section">
        <strong>Deductions</strong>
        <table>
            <tr>
                <td>Cash Loan</td>
                <td class="amount">- $500.00</td>
            </tr>
        </table>
        <div class="net-pay bold amount">Net Pay: $500.00</div>
    </div> --}}

    <!-- Earnings Section -->

    <!-- Earnings Section -->

{{-- @dd($payroll) --}}
    <table class="pay-table">
        <thead>
            <tr>
                <th class="label" style=" text-align: left;font-size: 16px; font-weight: bold;border-bottom: 1px solid black ">Earnings</th>
                <th style="text-align: left;border-bottom: 1px solid black">Rate</th>
                <th style="text-align: left;border-bottom: 1px solid black">Days</th>
                <th style="text-align: left;border-bottom: 1px solid black" colspan="2">this period</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label">Regular</td>
                <td><span class="highlight-cell">{{ number_format( $payroll->employee->daily_salary) }}</span></td>
                <td>{{$month->daysInMonth-$annualLeaves->sum('days')}}</td>
                <td colspan="2">{{ number_format( $payroll->employee->base_salary)     }}</td>
            </tr>

            @foreach ($payroll->benefits->where('type', 'allowance')->sortBy('built_in') as $allowance)
            {{-- @dd($allowance) --}}

            <tr>
                <td class="label">{{$allowance->title}}</td>
                <td>-</td>
                <td>-</td>
                <td colspan="2">{{$allowance->pivot->amount >0? number_format( $allowance->pivot->amount):$allowance->pivot->percent."%"." (".$allowance->on_change.")" }}</td>
            </tr>
        @endforeach
            {{-- <tr>
                <td class="label">R&R/Leave</td>
                <td>0.00</td>
                <td>{{$annualLeaves->sum('days')}} Of {{$leaveType?->days}} --- Remining Leaves {{$leaveType?->days- $annualLeaves->sum('days') }} Days</td>
                <td colspan="2">$record->total_deduction</td>
            </tr> --}}
            <tr class="">
                <td></td>
                <td style="background: #e0e0e0;border-bottom: 2px solid black;border-top:2px solid black " class="label">Gross Pay</td>
                {{-- @dd($payroll->total_allowance,$payroll->employee?->base_salary , $payroll->total_allowance ,) --}}
                <td style="background: #e0e0e0;text-align: right;border-bottom: 2px solid black;border-top:2px solid black "
                    colspan="3">{{ number_format($payroll->employee?->base_salary + $payroll->total_allowance )}} {{$currency}}</td>
            </tr>
        </tbody>
    </table>

    <!-- Deductions Section -->
    <div class="section-title deductions">Deductions</div>
    <table class="pay-table">
        <tbody>
        @foreach ($payroll->benefits->where('type', 'deduction')->sortBy('built_in') as $deduction)

            <tr>
                <td class="label">{{$deduction->title }}</td>
                <td></td>
                <td></td>
                <td style="text-align: center"
                    colspan="3">{{$deduction->pivot->amount >0? number_format( $deduction->pivot->amount):$deduction->pivot->percent."%"." (".$deduction->on_change.")" }}</td>
            </tr>
        @endforeach

        <tr class="">
            <td colspan="5"></td>

            <td style="background: #e0e0e0;border-bottom: 2px solid black;border-top:2px solid black " class="label">Net Pay</td>

            <td style="background: #e0e0e0;text-align: right;border-bottom: 2px solid black;border-top:2px solid black "
                colspan="2">{{ number_format($payroll->amount_pay,2) }} {{$currency}}</td>
        </tr>
        </tbody>
    </table>


    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
        <tr>
            <!-- t1 -->
            <td style="vertical-align: top; width: 70%; padding: 0; margin: 0;">
                <table style="border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style=" padding: 2px 6px 2px 0;">&nbsp;</td>
                    </tr>
                </table>
            </td>

            <!--t2 -->
            <td style="width: 30%;">
                <table class="">
                    <tbody>
                    <tr>
                        <td colspan="2" class="bold">Transaction number:</td>
                        <td

                            colspan="2">{{$payroll?->invoice?->number ? str_pad($payroll?->invoice?->number, 5, '0', STR_PAD_LEFT):" ---------" }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"  class="bold">Pay Date:</td>

                        <td colspan="3"> {{ $payroll->pay_date ?  \Carbon\Carbon::parse($payroll->pay_date)->format('d/m/Y'):" ---------"}}  </td>

                    </tr>
                    <tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr><tr>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                        <td>
                            <pre> </pre>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td style="font-size: 17px "><b>NON−NEGOTIABLE</b></td>
                    </tr>
                    </tbody>
                </table>
            </td>

        </tr>
    </table>

</body>

</html>
