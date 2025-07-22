@include('pdf.header', ['title'=>'Balance Sheet','titles' => ['']])

<div style="text-align: left; padding: 0; margin: 0;">
    <p style="margin: 0; padding: 0;text-align:center">As of {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : \Carbon\Carbon::parse(now())->format('Y-m-d') }}</p>
</div>
<table>
    <thead>
        <tr>
            <th>Assets ({{ PDFdefaultCurrency($company)}})</th>
            <th>Liabilities + Equity`s ({{ PDFdefaultCurrency($company)}})</th>
        </tr>
    </thead>
    @php
        $totalAsset=0;
    @endphp
    <tbody>
        <tr>

            <td>
                @foreach ($accounts['Assets'] as $key=> $asset)
                {{-- @dd($key , $asset[]) --}}
                    {{-- @if ($asset['sum']!= 0) --}}
                    <ul class="item-list">
                        <li style="font-weight: bold">
                            {{ $key }}:
                            {{ number_format($asset['sum']) }}
                        </li>
                            @foreach ($asset['item'] as $key => $Children)
                            {{--  @if ($difference != 0)  --}}
                            {{-- @dd($key,$Children) --}}
                            <li>
                                {{ $key }}:
                                {{ number_format($Children['sum']) }}
                            </li>
                        @php
                            $totalAsset+=$Children['sum'];
                        @endphp

                            @if (isset($asset['item']) && count($asset['item']))
                                @include('components.pdf.account-item', ['items' => $Children['item']])
                            @endif
                            {{--  @endif  --}}
                        @endforeach
                    </ul>
                    {{-- @endif --}}
                @endforeach
            </td>
            <td>
                @php
                    $incomes = $accounts['Income']['Income']['sum'];
                         $Expenses = $accounts['Expenses']['Expenses']['sum'];
                @endphp

                <ul class="item-list">
                    @foreach (['Liabilities', 'Equity'] as $type)
                        @php $items = $accounts[$type]; @endphp
                        @foreach ($items as $key => $item)
                            <li style="font-weight: bold">
                                @if($key === "Equity")
                                    Equity`s: {{ number_format($item['sum'] + $incomes + $Expenses) }}
                                @else
                                    {{ $key }}: {{ number_format($item['sum']) }}
                                @endif
                            </li>

                            @php
                                $innerItems = $item['item'];

                            @endphp

                            @include('components.pdf.account-item', ['items' => $innerItems])
                        @endforeach
                        @if ($key === "Equity")
                        <li style="font-weight: bold"> Total Earnings:{{number_format($incomes + $Expenses)}}</li>
                    @endif
                        <br>

                    @endforeach

                    @php

                       $sumAsset = $accounts['Assets']['Assets']['sum'];


                   $sumLib = $accounts['Liabilities']['Liabilities']['sum'];
                   $sumEq = $accounts['Equity']['Equity']['sum'];
                    @endphp
                    {{-- @dd($accounts['Income'],$accounts['Assets']['Assets']) --}}
                    {{--
                    @foreach ($accounts['Income'] as $inc)
                        @php
                            $incomes += $inc['sum'];
                        @endphp
                    @endforeach

                    @foreach ($accounts['Expenses'] as $exp)
                        @php
                            $Expenses += $exp['sum'];
                        @endphp
                    @endforeach --}}




                </ul>
            </td>

        </tr>

        <tr>
            <td>
                <strong>Total :</strong>


                {{ number_format($totalAsset) }}
            </td>
            <td>
                <strong>Total :</strong> {{ number_format($sumLib + $sumEq +($incomes + $Expenses) ) }}
            </td>
        </tr>
    </tbody>
</table>
@include('pdf.footer')
