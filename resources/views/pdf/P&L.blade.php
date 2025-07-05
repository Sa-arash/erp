@include('pdf.header', ['title'=>'Profit & Loss Statement','titles'=>[],'css'=>true])

<style>
    table {
        page-break-inside: auto;
    }
    table tr td{
        padding: 4px;
    }

    @page  {
        margin-left: 30px;
        margin-right: 30px;
    }
</style>
<p style="text-align: center">
     {{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}
</p>

@php
    $revenues = $report['IncomeTotal'] ?? 0;
    $expenses = $report['ExpensesTotal'] ?? 0;

    $revenueItems = $report['Income'] ?? [];
    $expenseItems = $report['Expense'] ?? [];

    $netProfit = $report['NetProfit'] ?? ($revenues - $expenses);
@endphp

<table style="width: 100%;">
    <thead>
    <tr>
        <th style="text-align: left;">Account</th>
        <th style="text-align: center;">Amount ({{ PDFdefaultCurrency($company) }})</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td colspan="2" style="background: #89e889"><strong>Income</strong></td>
    </tr>
    @foreach ($revenueItems as $category => $amount)
        <tr style="background: #89e889">
            <td>{{ $category }}</td>
            <td style="text-align: center;">{{ number_format($amount) }}</td>
        </tr>
    @endforeach
    <tr>
        <td><strong>Total Income</strong></td>
        <td style="text-align: center;"><strong>{{ number_format($revenues) }}</strong></td>
    </tr>

    <tr><td colspan="2" style="height: 10px;"></td></tr>

    <tr>
        <td colspan="2" style="background: #d94f4f"><strong>Expenses</strong></td>
    </tr>
    @foreach ($expenseItems as $category => $amount)
        <tr style="background: #d94f4f">
            <td>{{ $category }}</td>
            <td style="text-align: center;">{{ number_format($amount) }}</td>
        </tr>
    @endforeach
    <tr>
        <td><strong>Total Expenses</strong></td>
        <td style="text-align: center;"><strong>{{ number_format($expenses) }}</strong></td>
    </tr>

    <tr><td colspan="2" style="height: 10px;"></td></tr>

    <tr>
        <td><strong>Net Profit</strong></td>
        <td style="text-align: center;"><strong>{{ number_format($netProfit) }}</strong></td>
    </tr>
    </tbody>
</table>

@include('pdf.footer')
