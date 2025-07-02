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
    'title' => $title .' '. $month->format('M') . ' ' . $year,
    'titles' => [],
    'css' => false,
])
    <style>
        body {
            font-size: 12px;
            font-family: Arial;

        }
        .w-100{

            width: 100%;
        }
        .pay-table {
            width: 50% !important;
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

<body>
    <br>

    <div class="clearfix">
        <div class="company-info">
            {{-- <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 50px;"><br> --}}
            AREA TARGET GENERAL TRADING L.L.C<br>
            AL MERAIKHI TOWER 2, RIG AL BUTIN RD,<br>
            DEIRA, DUBAI, UAE<br>
            Email: <a href="mailto:{{$company->email_finance}}">{{$company->email_finance}}</a><br>
            Contact: {{$company->phone_finance}}
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
