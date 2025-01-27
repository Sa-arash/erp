<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: auto;
            border: 1px solid #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 14px;
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
    <div class="header">
        <h1>Gate Pass</h1>
        <p>SERVICES TEAM - UNC COMPOUND</p>
    </div>

    <div class="details">
        <table>
            <tr>
                <td>From:</td>
                <td>{{$takeOut->from}}</td>
                <td>To:</td>
                <td>{{$takeOut->to}}</td>
            </tr>
            <tr>
                <td>Requestor's Name:</td>
                <td>{{$takeOut->employee->fullName}}</td>
                <td>Badge Number:</td>
                <td>{{$takeOut->employee->ID_number}}</td>
            </tr>
            <tr>
                <td>Designation:</td>
                <td>{{$takeOut->employee->position?->title}}</td>
                <td>Department:</td>
                <td>{{$takeOut->employee->department?->title}}</td>
            </tr>
        </table>
    </div>

    <table class="items-table">
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

    <table class="status-table">
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
    <table class="status-table">
        <thead>
        <tr>

            <th>Name</th>
            <th>Signature</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Name: ________________________</td>
            <td>Signature: ________________________</td>
        </tr>
        <tr>
            <td>Name: ________________________</td>
            <td>Signature: ____________________</td>
        </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Nikhil Thapa</p>
        <p>Security Officer - UNC</p>
    </div>
</div>
</body>
</html>
