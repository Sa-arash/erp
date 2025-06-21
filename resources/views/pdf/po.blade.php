@include('pdf.header',
   ['titles'=>[''],'title'=>'Purchase Order','css'=>false])


<style>
    body{
        font-family: Arial, sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    table tr {
        width: 100% !important;
    }

    th, td {
        border: 1px solid #000;
        font-size: 12px;
        padding: 4px !important;
        text-align: center;
    }

    th {
        background-color: #817c7c;
        color: white;
    }

    .comments, .signature {
        margin-top: 20px;
    }




</style>

<body>
<table>


    <tr>
        <td style="text-align: left">PO Date: {{\Illuminate\Support\Carbon::create($po->date_of_delivery)->format('M j, Y / h:iA ')}}</td>
        <td style="text-align: left">PR No: ATGT/UNC {{$po->purchase_orders_number}}</td>
    </tr>
    <tr>
        <td style="text-align: left">Processed By Name: {{$po->employee?->fullName}}</td>
        <td style="text-align: left">
            Position: {{$po->employee?->position?->title}}</td>
    </tr>
    <tr>
        <td style="text-align: left">Currency: {{$po->currency->name}} ({{number_format($po->exchange_rate,4)}})  </td>
        <td style="text-align: left">Location of Delivery :  {{$po->location_of_delivery}}</td>
    </tr>

</table>
<table>
    <tr>
        <td colspan="2" style="text-align: left">Description : {{$po->description}}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>NO</th>
        <th>SKU</th>
        <th>Description</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Taxes</th>
        <th>Freights</th>
        <th>TEC</th>

    </tr>
    </thead>
    <tbody>
    @php
        $totalEstimated=0;
        $totalBudget=0;
        $i=1;
    @endphp
    @foreach($po->items as $item)
        @php
            $totalEstimated+=$item->unit_price;
            $totalBudget+=$item->total;
        @endphp


        <tr>
            <td rowspan="1">{{$i++}}</td>

            <td>   {{  $item->product->title."-".$item->product?->sku}}</td>
            <td>

                {{ $item->description }}
            </td>
            <td>{{$item->unit->title}}</td>
            <td>{{$item->quantity}}</td>
            <td>{{number_format($item->unit_price)}}</td>
            <td>{{$item->taxes}}%</td>
            <td>{{$item->freights}}%</td>
            <td>{{number_format($item->total)}}</td>

        </tr>

    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5">Total Cost</td>
        <td>{{number_format($totalEstimated)}}</td>
        <td >---</td>
        <td >---</td>
        <td>{{number_format($totalBudget)   }}</td>

    </tr>
    </tfoot>
</table>



<table style="border: none!important;" >
    <tr  style="border: none!important;">
        @foreach($po?->approvals->where('status','Approve') as $approve)

            <th style="border: none ;background: white !important;color: #1a202c">

                    @if($approve->position==="PO Logistic Head")
                        Prepared By
                        <br>
                    <hr>
                        {{$approve->employee->fullName}}
                        <br>{{$approve->employee?->position->title}}
                    @elseif($approve->position==="PO Verification")
                        Verification By
                        <br>
                    <hr>
                        {{$approve->employee->fullName}}
                        <br>{{$approve->employee?->position->title}}
                    @else
                        {{str_replace('PO','',$approve->position)}} By
                        <br>
                    <hr>
                        {{$approve->employee->fullName}}
                        <br>  {{$approve->employee?->position->title}}
                    @endif

            </th>
        @endforeach
    </tr>
    <tr style="border: none!important;background: white !important;">
        @foreach($po?->approvals->where('status','Approve') as $approve)

            <td style="border: none!important;text-align: center;background: white !important;color: #1a202c">
                @if ($approve->employee->media->where('collection_name','signature')->first()?->original_url  )
                    <img src="{!! $approve->employee->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;" alt="">

                @endif
            </td>
        @endforeach
    </tr>
    </table>
{{--@php--}}
{{--    $document=$po->invoice;--}}
{{--@endphp--}}
{{--<div style="text-align: left; padding: 0; margin: 0;">--}}
{{--    <p style="margin: 0; padding: 0;">Voc No: {{ $document->number }}</p>--}}
{{--    <p style="margin: 0; padding: 0;">Date: {{ \Carbon\Carbon::parse($document->date)->format('Y-m-d') }}</p>--}}
{{--    <p style="margin: 0; padding: 0;">Voc Description: {{ $document->name }}</p>--}}
{{--</div>--}}
{{--<table>--}}
{{--    <thead>--}}
{{--    <tr>--}}
{{--        <th>No</th>--}}
{{--        <th>Account Name</th>--}}
{{--        <th>Account Code</th>--}}
{{--        <th>Description</th>--}}
{{--        <th>Currency</th>--}}
{{--        <th>Debit Foreign</th>--}}
{{--        <th>Credit Foreign</th>--}}
{{--        <th>Exchange Rate</th>--}}
{{--        <th>Debit({{ PDFdefaultCurrency($company)}})</th>--}}
{{--        <th>Credit({{ PDFdefaultCurrency($company)}})</th>--}}
{{--    </tr>--}}
{{--    </thead>--}}
{{--    <tbody>--}}
{{--    @foreach ($document->transactions as $id => $transaction)--}}
{{--        <tr>--}}
{{--            <td>{{ $id + 1 }}</td>--}}
{{--            <td>{{ $transaction->account->name }}</td>--}}
{{--            <td>{{ $transaction->account->code }}</td>--}}
{{--            <td>{{ $transaction->description }}</td>--}}
{{--            <td>{{ $transaction->currency->name }}</td>--}}
{{--            <td>{{ number_format($transaction->debtor_foreign,2) }}</td>--}}
{{--            <td>{{ number_format($transaction->creditor_foreign,2) }}</td>--}}
{{--            <td>{{ number_format($transaction->exchange_rate,5) }}</td>--}}
{{--            <td>{{ number_format($transaction->debtor) }}</td>--}}
{{--            <td>{{ number_format($transaction->creditor) }}</td>--}}
{{--        </tr>--}}
{{--    @endforeach--}}

{{--    <tr>--}}
{{--        <td colspan="5">--}}
{{--            Total--}}
{{--        </td>--}}
{{--        <td>--}}
{{--            {{ number_format($document->transactions->sum('debtor_foreign')) }}--}}
{{--        </td>--}}
{{--        <td>--}}
{{--            {{ number_format($document->transactions->sum('creditor_foreign')) }}--}}
{{--        </td>--}}
{{--        <td></td>--}}
{{--        <td>--}}
{{--            {{ number_format($document->transactions->sum('debtor')) }}--}}
{{--        </td>--}}
{{--        <td>--}}
{{--            {{ number_format($document->transactions->sum('creditor')) }}--}}
{{--        </td>--}}
{{--    </tr>--}}
{{--    </tbody>--}}
{{--</table>--}}


</body>
</html>
