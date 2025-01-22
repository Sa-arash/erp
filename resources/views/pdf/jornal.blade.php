@include('pdf.header', ['titles' => ['Journal Report']])


@php
    $minDate = $transactions->min(function ($transaction) {
        return $transaction->invoice->date; // فرض می‌کنیم که 'date' تاریخ مربوط به invoice است
    });
    $maxDate = $transactions->max(function ($transaction) {
        return $transaction->invoice->date; // فرض می‌کنیم که 'date' تاریخ مربوط به invoice است
    });
@endphp
<div style="text-align: left; padding: 0; margin: 0;">
    <p style="margin: 0; padding: 0;">Date:
        {{ \Carbon\Carbon::parse($minDate)->format('Y-m-d') }} -
{{ \Carbon\Carbon::parse($maxDate)->format('Y-m-d') }}
    </p>
</div>

<table>
    <thead>
        <tr>
            {{-- <th>Row</th> --}}
            <th>Voc No</th>
            <th>Date</th>
            <th>Account Name</th>
            <th>Account Code</th>
            <th>Description</th>
            <th>Debit({{ $company->currency }})</th>
            <th>Credit({{ $company->currency }})</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($transactions as $id => $transaction)
            <tr>
                {{-- <td>{{ $id + 1 }}</td> --}}
                <td>{{ $transaction->invoice->number }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->invoice->date)->format('Y-m-d') }}</td>
                <td>{{ $transaction->account->name }}</td>
                <td>{{ $transaction->account->code }}</td>
                <td>{{ $transaction->description }}</td>
                <td>{{ number_format($transaction->debtor) }}</td>
                <td>{{ number_format($transaction->creditor) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="5">
                Total :
            </td>
            <td>
                {{ number_format($transactions->sum('debtor')) }}
            </td>
            <td>
                {{ number_format($transactions->sum('creditor')) }}
            </td>
        </tr>
    </tbody>
</table>
@include('pdf.footer')
