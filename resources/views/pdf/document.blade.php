@include('pdf.header', ['title'=>'Voucher Report','titles' => [''],'css'=>true])

<div style="text-align: left; padding: 0; margin: 0;">
    <p style="margin: 0; padding: 0;">Voc No: {{  str_pad($document->number, 5, '0', STR_PAD_LEFT) }}</p>
    <p style="margin: 0; padding: 0;">Date: {{ \Carbon\Carbon::parse($document->date)->format('d/F/Y') }}</p>
    <p style="margin: 0; padding: 0;">Voc Description: {{ $document->name }}</p>
</div>
<br>
<br>

<table style="margin-top: 20px !important;" >
    <thead>
        <tr>
            <th>No</th>
            <th>Account Name</th>
            <th>Account Code</th>
            <th>Description</th>
            <th>Debit({{ PDFdefaultCurrency($company)}})</th>
            <th>Credit({{ PDFdefaultCurrency($company)}})</th>
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
            <td colspan="4" style="text-align: center">
                <b>Total :</b>
            </td>
            <td>
                {{ number_format($document->transactions->sum('debtor'))  }} {{ PDFdefaultCurrency($company)}}
            </td>
            <td>
                {{ number_format($document->transactions->sum('creditor'))  }} {{ PDFdefaultCurrency($company)}}
            </td>
        </tr>
    </tbody>
</table>
@include('pdf.footer')
