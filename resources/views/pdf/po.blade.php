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
        background-color: #c9c1c1;
        color: #000000;
    }

    .comments, .signature {
        margin-top: 20px;
    }




</style>

<body>
<table>


    <tr>
        <td style="text-align: left">PO Date: {{\Illuminate\Support\Carbon::create($po->date_of_po)->format('M j, Y ')}}</td>
        <td style="text-align: left">PO No: ATGT/UNC {{$po->purchase_orders_number}}</td>
    </tr>
    <tr>
        <td style="text-align: left">Processed By : {{$po->employee?->fullName}}</td>
        <td style="text-align: left">
            Position: {{$po->employee?->position?->title}}</td>
    </tr>
    <tr>
        <td style="text-align: left">Location of Delivery :  {{$po->location_of_delivery}}</td>
        <td style="text-align: left">Date of Delivery :  {{$po->date_of_delivery ? \Illuminate\Support\Carbon::create($po->date_of_delivery)->format('M j, Y '):''}}</td>
    </tr>

</table>


<table>
    <thead>
    <tr>
        <th>NO</th>
        <th style="width: 15%">SKU</th>
        <th>Description</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Taxes</th>
        <th>Freights</th>
        <th>Vendor</th>
        <th>Currency</th>
        <th>ER</th>
        <th>Total Cost</th>

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
            $totalBudget+=$item->total;
        @endphp


        <tr>
            <td rowspan="1">{{$i++}}</td>

            <td style="padding: 1px">   {{  $item->product?->title."-".$item->product?->sku}}</td>
            <td  style="padding: 1px">

                {{ $item->description }}
            </td>
            <td style="padding: 1px">{{$item->unit->title}}</td>
            <td style="padding: 1px">{{$item->quantity}}</td>
            <td style="padding: 1px">{{number_format($item->unit_price)}}</td>
            <td style="padding: 1px">{{$item->taxes}}%</td>
            <td style="padding: 1px">{{$item->freights}}%</td>
            <td style="padding: 1px">{{$item->vendor?->name}}</td>
            <td style="padding: 1px"> {{$item->currency?->name}}   </td>
            <td style="padding: 1px">{{number_format($item->exchange_rate,2)}}</td>

            <td>{{number_format($item->total)}}</td>

        </tr>

    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="11">Grand Total Cost</td>


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
                    Verified By
                        <br>
                    <hr>
                        {{$approve->employee->fullName}}
                        <br>{{$approve->employee?->position->title}}
                    @else
                    Approved By
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
</body>

