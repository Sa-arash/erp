@include('pdf.header',[
'title'=>'Salary Deduction Letter',
'titles'=>[]
])
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            padding: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 18px;
        }
        .ref-number {
            text-align: right;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 15px;
        }
        .highlight-yellow {
            background-color: #fafafa;
            font-weight: bold;
        }
        .signature-block {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 30%;
            text-align: center;
        }
        .signature .name {
            font-weight: bold;
            margin-top: 40px;
        }
        .label {
            font-weight: bold;
        }
        .email-vertical {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            position: absolute;
            left: 0;
            top: 100px;
            font-size: 12px;
            color: gray;
        }
        .address {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            position: absolute;
            left: 0;
            bottom: 40px;
            font-size: 12px;
            color: gray;
        }
        .signature-block {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature {
            text-align: center;
            width: 30%;
        }

        .highlight-yellow {
            font-weight: bold;
        }
    </style>
</head>
<body>


<div class="ref-number">Ref No: UNC-HR-SDL-001</div>

<div class="section">
    <div><span class="label">Date:</span> <span class="highlight-yellow">{{\Illuminate\Support\Carbon::make($loan->request_date)->format('Y-m-d')}}</span></div>
    <div><span class="label">To:</span> <span class="highlight-yellow">{{$loan->employee->fullName}}</span> (employee name)</div>
    <div><span class="label">From:</span> {{$loan->employee?->department?->title}}</div>
    <div><span class="label">Subject:</span> <strong>Salary Deduction Letter for Loan Repayment</strong></div>
</div>

<div class="section">
    Dear <span class="highlight-yellow">{{$loan->employee->fullName}}</span>,
</div>

<div class="section">
    This letter is to inform you that in response with your request for your cash loan from the
    company’s petty cash funds to be deducted from your monthly salary. The salary deduction will
    be on installment basis for <span class="highlight-yellow">{{$loan->number_of_installments}} months</span> starting from
    <span class="highlight-yellow">{{\Illuminate\Support\Carbon::make($loan->first_installment_due_date)->format('M Y')}} payroll period until {{\Illuminate\Support\Carbon::make($loan->first_installment_due_date)->addMonths($loan->number_of_installments)->format('M Y')}}</span>.
</div>

<div class="section">
    The details of the loan repayment are as follows:
    <ul>
        <li>Principal Loan Amount: {{$loan->employee->currency?->name}} {{number_format($loan->amount)}}</li>
        <li>Deduction Amount per Month: {{$loan->employee->currency?->name}} {{number_format($loan->amount/$loan->number_of_installments)}}</li>
        <li>Deduction Start Date: {{\Illuminate\Support\Carbon::make($loan->first_installment_due_date)->format('M Y')}} payroll</li>
        <li>Deduction End Date: {{\Illuminate\Support\Carbon::make($loan->first_installment_due_date)->addMonths($loan->number_of_installments)->format('M Y')}} payroll</li>
        <li>Total Deduction Period: {{$loan->number_of_installments}} months</li>
    </ul>
</div>

<div class="section">
    The deductions will be made directly from your monthly salary which will be reflected in your
    paystub accordingly.
</div>

<div class="section">
    By affixing your signature, you are in agreement to the above loan repayment schedule.
</div>


<table style="width: 100%; border-collapse: collapse; margin-top: 50px;border: none;">
    <tr style="border: none;">
        <td style="text-align: center; width: 33.33%;border: none;">
            <div>{{$loan->admin?->fullName}}</div>
            <div>Sr. Admin/HR Asst</div>
            <div style="margin-top: 20px;">_________________</div>
            <div>Date:{{\Carbon\Carbon::make($loan->approve_admin_date)->format('Y-m-d H:iA')}} </div>
        </td>
        <td style="text-align: center; width: 33.33%;border: none;">
            <div class="highlight-yellow" style="font-weight: bold;">{{$loan->employee->fullName}}</div>
            <div>Employee</div>
            <div style="margin-top: 20px;">_________________</div>
            <div>Date: {{\Carbon\Carbon::make($loan->request_date)->format('Y-m-d H:iA')}}</div>
        </td>
        @if($loan->finance)
        <td style="text-align: center; width: 33.33%;border: none;">
            <div>{{$loan->finance?->fullName}}</div>
            <div>Sr. Finance & Admin/HR Officer</div>
            <div style="margin-top: 20px;">_________________</div>
            <div>Date  {{ $loan?->approve_finance_date? \Carbon\Carbon::make($loan?->approve_finance_date)->format('Y-m-d H:iA'):'--'}}</div>
        </td>
            @endif
    </tr>
</table>


</body>
</html>
