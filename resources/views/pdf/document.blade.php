@include('pdf.header', ['titles' => ['Voucher Report']])

<div style="text-align: left; padding: 0; margin: 0;">
    <p style="margin: 0; padding: 0;">Voc No: {{ $document->number }}</p>
    <p style="margin: 0; padding: 0;">Date: {{ \Carbon\Carbon::parse($document->date)->format('Y-m-d') }}</p>
    <p style="margin: 0; padding: 0;">Voc Desctiption: {{ $document->name }}</p>
</div>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Account Name</th>
            <th>Account Code</th>
            <th>Description</th>
            <th>Debit({{ $company->currency }})</th>
            <th>Credit({{ $company->currency }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($document->transactions as $id => $transaction)
            <tr>
                <td>{{ $id + 1 }}</td>
                <td>{{ $transaction->account->name }}</td>
                <td>{{ $transaction->account->code }}</td>
                <td>{{ $transaction->description }}</td>
                <td>{{ number_format($transaction->debtor) }}</td>
                <td>{{ number_format($transaction->creditor) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="4">
                Total :
            </td>
            <td>
                {{ number_format($document->transactions->sum('debtor')) }}
            </td>
            <td>
                {{ number_format($document->transactions->sum('creditor')) }}
            </td>
        </tr>
    </tbody>
</table>
@include('pdf.footer')
