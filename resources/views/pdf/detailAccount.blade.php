
    @include('pdf.header',
    ['titles'=>[$accountTitle??'Account Report'],'title'=>'Account Report'])

    <br>

@if(isset($startDate))
<p style="text-align: center">{{ $startDate->format('Y/m/d') . '     -    ' . $endDate->format('Y/m/d') }}</p>
@endif
<table>
    <thead>
    <tr>
        <th>Voc No</th>
        <th>Date</th>
        <th>Account Name</th>
        <th>Account Code</th>
        <th>Voc Desctiption</th>
        <th>Debit</th>
        <th>Credit</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalCreditor=0;
        $totalDebtor=0;
    @endphp

    @foreach ($transactions as $transaction)
        <tr>
            <td>{{ $transaction->invoice->number }}</td>
            <td class="nowrap-text">{{ (new DateTime($transaction->invoice->date))->format('Y/m/d') }}</td>
            <td>{{ $transaction->account->name }}</td>
            <td>{{ $transaction->account->code }}</td>
            <td>{{ $transaction->invoice->name }}</td>
            <td>{{ number_format($transaction->debtor) }}</td>
            <td>{{ number_format($transaction->creditor) }}</td>
        </tr>
        @php
            $totalDebtor+=$transaction->debtor;
            $totalCreditor+=$transaction->creditor;
        @endphp
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" style="text-align: center">Total</td>
        <td style="text-align: center">{{ number_format($totalDebtor) }}</td>
        <td style="text-align: center">{{ number_format($totalCreditor) }}</td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center">Balance</td>
        <td colspan="2" style="text-align: center">{{ number_format($totalDebtor - $totalCreditor) }}</td>
    </tr>
    </tfoot>
</table>
@include('pdf.footer')
