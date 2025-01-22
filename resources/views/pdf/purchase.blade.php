@include('pdf.header',
   ['titles'=>['']])
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        body {
            font-family: Vazir, sans-serif;
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
        }

        .comments, .signature {
            margin-top: 20px;
        }

        @page {
            margin: 20px;
        }


    </style>
</head>
<body>
<h1>Purchase Request Form</h1>
<table>


    <tr>
        <td style="text-align: left">Date of
            Request: {{\Illuminate\Support\Carbon::create($pr->request_date)->format('Y/m/d')}}</td>
        <td style="text-align: left">PR No: {{$pr->purchase_number}}</td>
    </tr>
    <tr>
        <td style="text-align: left">Employee name: {{$pr->employee?->fullName}}</td>
        <td style="text-align: left">Employee
            <Posit></Posit>
            ion: {{$pr->employee?->position?->title}}</td>
    </tr>
    <tr>
        <td style="text-align: left">Department: {{$pr->employee->department?->title}}</td>
        <td style="text-align: left">Location: {{$pr->employee->structure->title}}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>SKU</th>
        <th>Item Description</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Estimated Cost</th>
        <th>Total Estimated Cost</th>
        <th>Stock In</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalEstimated=0;
        $totalBudget=0;

    @endphp
    @foreach($pr->items as $item)
        @php
            $totalEstimated+=$item->estimated_unit_cost;
            $totalBudget+=$item->quantity *$item->estimated_unit_cost;
        @endphp
        <tr>
            <td>{{$item->product?->sku}}</td>
            <td>{{$item->description}}</td>
            <td>{{$item->unit->title}}</td>
            <td>{{$item->quantity}}</td>
            <td>{{number_format($item->estimated_unit_cost)}}</td>
            <td>{{number_format($item->quantity *$item->estimated_unit_cost)}}</td>
            <td>{{$item->warehouse_decision==="needs_purchase"?"Need Purchase" :"In Stack"}}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="4">Total Cost</td>
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
        <th>name</th>
        <th>Position</th>
        <th>Duty Station</th>
        <th colspan="2">Signature</th>
    </tr>
    <tr>
        <td style="text-align: center"><p>{{$pr->employee?->fullName}}</p></td>
        <td style="text-align: center"><p> {{$pr->employee?->position?->title}}</p></td>
        <td style="text-align: center   "><p> {{$pr->employee->structure?->title}}</p></td>
        <td style="text-align: center" colspan="2">
            @if(file_exists($pr->employee->signature_pic))
                <img src="{!!   public_path('images/'.$pr->employee->signature_pic)!!}"
                     style="border-radius: 50px ; width: 80px;" alt="">
            @endif
        </td>
    </tr>


</table>

<table style="background: #ffffff !important;">
    <tr>
        <td style="text-align: start">
            <p>Attested by: {{$pr->employee?->department?->employee?->fullName}} </p>
        </td>
        <td style="text-align: start">
            @if($pr->employee->department?->employee?->signature_pic)
            <img src="{!!   public_path('images/'.$pr->employee->department?->employee?->signature_pic)!!}" style="border-radius: 50px ; width: 80px;" alt="">
            @endif
        </td>
    </tr>
    <tr>
        <td style="text-align: start;background: #ffffff !important">
            <p>Verified by: (Government/Stock) </p>
        </td>
        <td style="text-align: start;background: #ffffff !important">
            <img src="{!!   public_path('img/images (1).png')!!}" style="border-radius: 50px ; width: 80px;" alt="">
        </td>
    </tr>
    <tr>
        <td style="text-align: start">
            <p>Approved by: _____</p>
        </td>
        <td style="text-align: start">
            <img src="{!!   public_path('img/images (1).png')!!}" style="border-radius: 50px ; width: 80px;" alt="">
        </td>
    </tr>
</table>
</body>
</html>
