@include('pdf.header',
   ['titles'=>[''],'title'=>'Goods Receipt Note','css'=>false])



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
<table >


    <tr>

        <td style="text-align: right;border: none;width: 80%">
            <p>GRN No : ATGT/UNC/{{$GRN->number}}</p>
            <p>Date Received : {{\Illuminate\Support\Carbon::make($GRN->received_date)->format('d/M/Y')}}</p>
            <p>PR No : ATGT/UNC/{{$GRN->purchaseOrder?->purchase_orders_number}}</p>
            <p>PO No : ATGT/UNC/{{$GRN->purchaseOrder->purchaseRequest?->purchase_number}}</p>
        </td>

    </tr>

</table>


<table>
    <thead>
    <tr>
        <th>NO</th>
        <th style="width: 20%">SKU</th>
        <th>Description</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Vendor</th>
        <th>Currency</th>
        <th>Exchange Rate</th>
        <th>Employee Ordered</th>
        <th>TEC</th>

    </tr>
    </thead>
    <tbody>
    @php
        $totalEstimated=0;
        $totalBudget=0;
        $i=1;
        $totalQTY=0;
    @endphp
    @foreach($GRN->items as $item)
        @php
            $totalEstimated+=$item->unit_price;
            $totalBudget+=$item->total;
            $totalQTY+=$item->quantity;
        @endphp


        <tr>
            <td rowspan="1">{{$i++}}</td>

            <td>   {{  $item->product?->title."-".$item->product?->sku}}</td>
            <td>

                {{ $item->description }}
            </td>
            <td>{{$item->unit->title}}</td>
            <td>{{$item->quantity}}</td>
            <td>{{number_format($item->unit_price)}}</td>
            <td>{{$item->vendor?->name}}</td>
            <td style=""> {{$item->currency?->name}}   </td>
            <td style="">{{number_format($item->exchange_rate,4)}}</td>
            <td style="">{{$item->employee?->fullName}}</td>

            <td>{{number_format($item->total)}}</td>

        </tr>

    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="4">GRN Total</td>
        <td>{{number_format($totalQTY)}}</td>
        <td>{{number_format($totalEstimated)}}</td>
        <td >---</td>
        <td >---</td>
        <td >---</td>
        <td >---</td>
        <td>{{number_format($totalBudget)   }}</td>

    </tr>
    </tfoot>
</table>



<table style="border: none!important;" >
    <tr  style="border: none!important;">
        @php
        $employees=\App\Models\Employee::query()->with('media')->whereIn('id',$GRN->items->pluck('employee_id')->unique())->get();
        @endphp
        @foreach($employees as $ordere)

            <th style="border: none ;background: white !important;color: #1a202c">
                    Purchased By
                    <br>
                    <hr>
                    {{$ordere->fullName}}
                    <br>

            </th>
        @endforeach
        <th style="border: none ;background: white !important;color: #1a202c">
            Received By
            <br>
            <hr>
            {{$GRN->purchaseOrder?->purchaseRequest?->employee->fullName}}
                <br>
        </th>
        <th style="border: none ;background: white !important;color: #1a202c">
           Stock Department
            <br>
            <hr>
            {{$GRN->manager->fullName}}
            <br>
        </th>
        <th style="border: none ;background: white !important;color: #1a202c">
            Finance Department
            <br>
            <hr>

            {{$GRN->purchaseOrder?->finance?->fullName ??'-'}}
            <br>
        </th>
    </tr>
    <tr style="border: none!important;background: white !important;">
        @foreach($employees as $ordere)
            <td style="border: none!important;text-align: center;background: white !important;color: #1a202c">
                @if (file_exists($ordere->media->where('collection_name','signature')->first()?->getPath())  )
                    <img src="{!! $ordere->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;" alt="">
                @endif
            </td>
        @endforeach
            <td style="border: none!important;text-align: center;background: white !important;color: #1a202c">
                @if (file_exists($GRN->purchaseOrder?->purchaseRequest?->employee->media->where('collection_name','signature')->first()?->getPath())  )
                    <img src="{!! $GRN->purchaseOrder?->purchaseRequest?->employee->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;" alt="">
                @endif
            </td>
            <td style="border: none!important;text-align: center;background: white !important;color: #1a202c">
                @if (file_exists($GRN->manager->media->where('collection_name','signature')->first()?->getPath())  )
                    <img src="{!! $GRN->manager->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;" alt="">
                @endif
            </td>
            <td style="border: none!important;text-align: center;background: white !important;color: #1a202c">
                @if (file_exists($GRN->purchaseOrder->finance?->media->where('collection_name','signature')->first()?->getPath())  )
                    <img src="{!! $GRN->purchaseOrder->finance?->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;" alt="">
                @endif
            </td>

    </tr>
</table>
</body>

