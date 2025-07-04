


    @include('pdf.header', ['titles' => [''],'title'=>'Trial Balance'])
    <div style="text-align: left; padding: 0; margin: 0;">

        <p style="margin: 0; padding: 0;text-align:center">As of {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : \Carbon\Carbon::parse(now())->format('Y-m-d') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th rowspan="2">Account Code</th>
                <th rowspan="2">Account Name</th>
                <th colspan="2">Account Balance</th>
            </tr>
            <tr>

                <th>Debtor({{ PDFdefaultCurrency($company)}})</th>
                <th>Creditor({{ PDFdefaultCurrency($company)}})</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
                @if ($account->transactions->sum('debtor') != 0 || $account->transactions->sum('creditor') != 0)
                    <tr>
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td>{{ number_format($account->type == 'debtor' ? $account->transactions->sum('debtor') - $account->transactions->sum('creditor') : 0) }}
                        </td>
                        <td>{{ number_format($account->type == 'creditor' ? $account->transactions->sum('creditor') - $account->transactions->sum('debtor') : 0) }}
                        </td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td colspan="2">Total:</td>

                <td>
                    {{ number_format(
                        $accounts->map(
                                fn($item) => $item->type == 'debtor'
                                    ? $item->transactions->sum('debtor') - $item->transactions->sum('creditor')
                                    : 0,
                            )->sum(),
                    ) }}
                </td>
                <td>{{ number_format(
                    $accounts->map(
                            fn($item) => $item->type == 'creditor'
                                ? $item->transactions->sum('creditor') - $item->transactions->sum('debtor')
                                : 0,
                        )->sum(),
                ) }}
                </td>
            </tr>
        </tbody>
    </table>
    @include('pdf.footer')
