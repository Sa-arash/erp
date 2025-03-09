@include('pdf.header', ['titles' => ['Balance Sheet']])

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
                            {{--  @endif  --}}
                        @endforeach
                    </ul>
                    {{-- @endif --}}
                @endforeach
            </td>
            <td>
                <ul class="item-list">
                    @foreach (['Liabilities', 'Equity'] as $type)
                        @php
                            // دریافت لیست مربوط به Libility یا Equity
                            $items = $accounts[$type];
                        @endphp
                        @foreach ($items as $key => $item)
                            {{-- @if ($item['sum'] != 0) --}}
                                <li style="font-weight: bold">
                                    @if($key==="Equity")
                                        Equity`s
                                    @else
                                        {{ $key }}:
                                    @endif

                                    {{ number_format($item['sum']) }}

                                </li>
                                {{--  @dd($items)  --}}
                                @foreach ($item['item'] as $key => $credit)
                                    <li>
                                        @if($key==="Equity")
                                            Equity`s
                                        @else
                                            {{ $key }}:
                                        @endif
                                        {{ number_format($credit['sum']) }}
                                    </li>
                                @endforeach
                            {{-- @endif --}}
                        @endforeach
                        <br>
                    @endforeach
                     @php
                        $incomes = $accounts['Income']['Income']['sum'];
                        $Expenses = $accounts['Expenses']['Expenses']['sum'];
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



                    <li style="font-weight: bold">
                        Profit or Loss: {{ number_format($incomes - $Expenses) }}
                    </li>
                </ul>
            </td>

        </tr>

        <tr>
            <td>
                <strong>Total :</strong>


                <!-- محاسبه مجموع برای Assets -->
                {{-- @foreach ($accounts['Assets'] as $asset)
                    @php
                        $sumAsset += $asset['sum']; // جمع مقادیر
                    @endphp
                @endforeach
                {{--  @dd($accounts,$sumAsset)  --}}

                <!-- محاسبه مجموع برای Liabilities -->
                {{-- @foreach ($accounts['Liabilities'] as $liability)
                    @php
                        $sumLib += $liability['sum']; // جمع مقادیر
                    @endphp
                @endforeach

                <!-- محاسبه مجموع برای Equity -->
                @foreach ($accounts['Equity'] as $equity)
                    @php
                        $sumEq += $equity['sum']; // جمع مقادیر
                    @endphp
                @endforeach --}}

                {{ number_format($sumAsset) }}
            </td>
            <td>
                <strong>Total :</strong> {{ number_format($sumLib + $sumEq + ($incomes - $Expenses)) }}
            </td>
        </tr>
    </tbody>
</table>
@include('pdf.footer')
