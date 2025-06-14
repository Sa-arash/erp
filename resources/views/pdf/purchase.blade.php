@include('pdf.header',
   ['titles'=>[''],'title'=>'Purchase Request','css'=>false])
    <style>
        body{
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table tr {
            width: 100% !important;
        }

        th, td {
            border: 1px solid #000;

            padding: 8px;
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
        <td style="text-align: left"> <span style="font-weight: bold">Date:</span>  {{\Illuminate\Support\Carbon::create($pr->request_date)->format('M j, Y / h:iA ')}}</td>
        <td style="text-align: left"><strong style="font-weight: bold" >Reg.No</strong>: ATGT/UNC {{$pr->purchase_number}}</td>
    </tr>
    <tr>
        <td style="text-align: left"><span  style="font-weight: bold">Requestor Name:</span>  {{$pr->employee?->fullName}}</td>
        <td style="text-align: left">
            <span  style="font-weight: bold"> Position:</span>
             {{$pr->employee?->position?->title}}</td>
    </tr>
    <tr>
        <td style="text-align: left"><span  style="font-weight: bold">Department:</span> {{$pr->employee->department?->title}}</td>
        <td style="text-align: left"> <span  style="font-weight: bold">Location:</span> {{$pr->employee?->warehouse?->title ." , ".  $pr->employee?->structure?->title}}</td>
    </tr>

</table>
<table>
    <tr>
        <td colspan="2" style="text-align: left"><span  style="font-weight: bold">Description :</span>  {{$pr->description}}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th colspan="8"  style="font-size: 18px"> Details</th>
    </tr>
    <tr>
        <th>NO</th>
        <th>SKU</th>
        <th>Description</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>EST Cost</th>
        <th>TEC</th>
        <th>Stock Balance</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalEstimated=0;
        $totalBudget=0;
        $i=1;
    @endphp
    @foreach($pr->items as $item)
        @php
            $totalEstimated+=$item->estimated_unit_cost;
            $totalBudget+=$item->quantity *$item->estimated_unit_cost;
        @endphp


        <tr>
            <td rowspan="1">{{$i++}}</td>

            <td>   {{ $item->product?->sku.' - '. $item->product->title}}</td>
            <td>
           {{ $item->description }}
            </td>
            <td>{{$item->unit->title}}</td>
            <td>{{$item->quantity}}</td>
            <td>{{number_format($item->estimated_unit_cost)}}</td>
            <td>{{number_format($item->quantity *$item->estimated_unit_cost)}}</td>
            <td>{{$item->product?->assets->where('status','inStorageUsable')->count()}}</td>
        </tr>

    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5">Total Estimated Cost </td>
        <td>{{number_format($totalEstimated)}}</td>
        <td>{{number_format($totalBudget)   }}</td>
        <td></td>
    </tr>
    </tfoot>
</table>

<table>

    @if($pr->ceo_comment !== null or $pr->general_comment !== null)
        <tr>
            <th colspan="5"><strong>Comments</strong></th>
        </tr>
    @endif

    @if($pr->ceo_comment !== null)
        <tr>
            <td colspan="5" style="text-align: start">{{$pr->ceo_comment}}</td>
        </tr>
    @endif
    @if($pr->general_comment !== null)
        <tr>
            <td colspan="5" style="text-align: start">{{$pr->general_comment}}</td>
        </tr>
    @endif


    <tr>
        <th colspan="5"><p>Requested by</p></th>
    <tr>
        <th>Name</th>
        <th>Position</th>
        <th>Duty Station</th>
        <th colspan="2">Signature</th>
    </tr>
    <tr>
        <td style="text-align: center"><p>{{$pr->employee?->fullName}}</p></td>
        <td style="text-align: center"><p> {{$pr->employee?->position?->title}}</p></td>
        <td style="text-align: center   "><p> {{$pr->employee?->warehouse?->title." , ". $pr->employee->structure?->title}}</p></td>
        <td style="text-align: center" colspan="2">
            @if($pr->employee->media->where('collection_name','signature')->first()?->getPath() !== null)
                <img src="{!!   $pr->employee->media->where('collection_name','signature')->first()->getPath()!!}"
                     style="border-radius: 50px ; width: 80px;" alt="">
            @endif
        </td>
    </tr>


</table>

<table style="border: none!important;" >
    <tr  style="border: none!important;">
        @foreach($pr?->approvals->where('status','Approve') as $approve)
        <th style="border: none!important;background: white !important;color: #1a202c">
            @if($approve->position==="PR Verification")
                Verified By
                <br>  {{$approve->employee?->position->title}}
            @elseif($approve->position==="PR Warehouse")
                Warehouse/Storage
                <br>  {{$approve->employee?->fullName}}
            @else
                {{str_replace('PR','',$approve->position)}}
                <br>  {{$approve->employee?->position->title}}
            @endif
               </th>
        @endforeach
    </tr>
    <tr style="border: none!important;background: white !important;">
        @foreach($pr?->approvals->where('status','Approve') as $approve)
        <td style="border: none!important;text-align: center;background: white !important;color: #1a202c">
            @if ($approve->employee->media->where('collection_name','signature')->first()?->original_url  )
                <img src="{!! $approve->employee->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;" alt="">

            @endif
        </td>
        @endforeach
    </tr>

</table>
</body>
</html>
