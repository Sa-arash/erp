<title>Gate Pass</title>
@include('pdf.header',
   ['titles'=>['Gate Pass']])
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            width: 100%;
            margin: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12px;
        }

        .details {
            margin-bottom: 20px;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 5px;
        }

        .details td:first-child {
            width: 20%;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .items-table th {
            background-color: #f4f4f4;
        }

        .reason-section {
            margin-bottom: 20px;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .status-table th, .status-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .status-table th {
            background-color: #f4f4f4;
        }

        .footer {
            text-align: right;
            margin-top: 20px;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container">


    <div style="border: 0!important;">
        <table style="border: 0!important;">
            <tr style="border: 0!important;">
                <td style="border: 0!important;">From:{{$takeOut->from}}</td>
                <td style="border: 0!important;">To: {{$takeOut->to}}</td>

            </tr>
            <tr style="border: 0!important;">
                <td style="border: 0!important;">Requestor's Name: {{$takeOut->employee->fullName}}</td>
                <td style="border: 0!important;">Badge Number: {{$takeOut->employee->ID_number}}</td>
            </tr >
            <tr style="border: 0!important;">
                <td style="border: 0!important;">Designation: {{$takeOut->employee->position?->title}}</td>
                <td style="border: 0!important;">Department:{{$takeOut->employee->department?->title}}</td>
            </tr>
        </table>
    </div>

    <table style="border: 0!important;">
        <thead>
        <tr>
            <th>SN</th>
            <th>Item Description</th>
            <th>Remarks</th>
        </tr>
        </thead>
        <tbody>
        @foreach($takeOut->items as $item)
        <tr>
            <td>1</td>
            <td>{{$item->asset->product->title." (".$item->asset->product->sku." )".$item->asset->brand->title."  " .$item->asset->model}}</td>
            <td>{{$item->remarks}}</td>
        </tr>
        @endforeach

        </tbody>
    </table>

    <div class="reason-section">
        <p><strong>Reason for Taking out:</strong> {{$takeOut->reason}}</p>
    </div>

    <table class="">
        <thead>
        <tr>
            <th>Status</th>
            <th>Type</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{$takeOut->status}}</td>
            <td>{{$takeOut->type}}</td>
        </tr>
        </tbody>
    </table>
    <table class="">
        <thead>
        <tr>
            <th>Name</th>
            <th>Designation</th>
            <th>Signature</th>
        </tr>
        </thead>
        <tbody>
        @foreach($takeOut->approvals->where('status','Approve') as $approve)
            <tr>
                <td>{{$approve->employee->fullName}}</td>
                <td>{{$approve->position}}</td>
                <td>@if($approve->employee?->signature_pic) <img
                        src="{{public_path('images/'.$approve->employee?->signature_pic)}}"
                        style="width: 100px;height: 60px" alt=""> @endif</td>
            </tr>
        @endforeach

        </tbody>
    </table>

    <div class="footer">
        <p></p>
        <p>Security Officer - {{$company->title}}</p>
    </div>

</div>
</body>
