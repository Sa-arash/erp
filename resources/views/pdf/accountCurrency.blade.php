
@include('pdf.header',
['titles'=>[$accountTitle??'Account Report'],'css'=>true,'title'=>'Account Report'])

<br>

<style>
    @page  {
        margin: 12px   ;
    }
</style>
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
        <th>Document Description</th>
        <th>Debit {{$transactions[0]?->account?->currency?->symbol}}</th>
        <th>Credit{{$transactions[0]?->account?->currency?->symbol}}</th>
        @if(count($accounts) === 1)
            <th>Balance </th>
        @endif
    </tr>
    </thead>
    <tbody>
    @php
        $totalCreditor=0;
        $totalDebtor=0;
        $totalBalance =0
    @endphp

    @foreach ($transactions as $transaction)
        <tr>
            <td>{{ $transaction->invoice->number }}</td>
            <td class="nowrap-text">{{ (new DateTime($transaction->invoice->date))->format('Y/m/d') }}</td>
            <td>{{ $transaction->account->name }}</td>
            <td>{{ $transaction->account->code }}</td>
            <td>{{ $transaction->invoice->name }}</td>
            <td>{{ number_format($transaction->debtor_foreign) }}</td>
            <td>{{ number_format($transaction->creditor_foreign) }}</td>
            @if(count($accounts) === 1)

                <td>{{ number_format(
                $totalBalance + ( $transaction->debtor_foreign-$transaction->creditor_foreign)
                ) }}</td>
            @endif
        </tr>
        @php
            $totalBalance = $totalBalance + ( $transaction->debtor_foreign-$transaction->creditor_foreign);
                $totalDebtor+=$transaction->debtor_foreign;
                $totalCreditor+=$transaction->creditor_foreign;
        @endphp
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" style="text-align: center">Total</td>
        <td style="text-align: center">{{ number_format($totalDebtor) }}</td>
        <td style="text-align: center">{{ number_format($totalCreditor) }}</td>
        @if(count($accounts) === 1)

            <td style="text-align: center">{{ number_format($totalBalance) }}</td>
    </tr>
    @endif
    {{--  <tr>
        <td colspan="5" style="text-align: center">Balance</td>
        <td colspan="2" style="text-align: center">{{ number_format($totalDebtor - $totalCreditor) }}</td>
    </tr>  --}}
    </tfoot>
</table>
@include('pdf.footer')
