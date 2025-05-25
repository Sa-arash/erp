<!doctype html>
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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            direction: ltr;
            background-color: #ffffff;
            font-size: 12px
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid ;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
            border-bottom: 2px solid #ddd;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) td {
            background-color: #ffffff;
        }

        tr:hover td {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .totals div {
            margin-left: 20px;
            font-weight: bold;
        }



        @media print {
            body {
                background-color: white;
                font-size: 12px;
            }

            table {
                page-break-inside: avoid;
            }

            th,
            td {
                border: 1px solid #000;
            }

            th {
                background-color: #333 !important;
                color: #fff !important;
            }

            tr:nth-child(even) td {
                background-color: #e8e8e8 !important;
            }

            tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }


        }


        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 15px;
            /* کوچک‌تر کردن اندازه فونت تیتر اصلی */
        }

        h2 {

            /* کوچک‌تر کردن اندازه فونت تیترهای شرکت و عناوین */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            /* کوچک‌تر کردن فونت جدول */
        }

        th,
        td {
            padding: 10px;
            /* کاهش اندازه padding برای تراکم بیشتر محتوا */
            text-align: left;
            font-size: 12px;
            /* کوچک‌تر کردن فونت سلول‌ها */
        }

        th {
            background-color: #4CAF50;
            color: white;
            border-bottom: 2px solid #ddd;
            font-size: 13px;
            /* کوچک‌تر کردن فونت تیترهای جدول */
        }

        .totals div {
            margin-left: 20px;
            font-weight: bold;
            font-size: 12px;
            /* کوچک‌تر کردن فونت قسمت مجموع‌ها */
        }



        @media print {
            body {
                background-color: white;
                font-size: 12pt;
            }

            table {
                page-break-inside: avoid;
            }

            th,
            td {
                border: 1px solid #000;
                font-size: 10pt;
            }

            th {
                background-color: #333 !important;
                color: #fff !important;
                font-size: 10pt;
            }

            tr:nth-child(even) td {
                background-color: #e8e8e8 !important;
            }

            tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }
        }
        @page {
            margin: 20px;
        }
    </style>
</head>
<body>
<table >
    <tr >
        <td style="border: none;width: 20%; text-align: left; padding-left: 10px;">

        </td>
        <td  style="border: none;text-align: center; vertical-align: middle; width: 40%;">
            <h4 style="margin: 0; padding: 0; font-size: 18px; white-space: nowrap; display: inline-block;">
                {{ $company?->title_security }}
            </h4>
        </td>
        <td style="border: none;width: 20%; text-align: right; padding-right: 10px;">
            @if($company?->logo_security)
                <img src="{!! public_path('images/' . $company?->logo_security) !!}" style="padding: 0; border-radius: 50px ; width: 100px;">
            @endif
        </td>
    </tr>
</table>
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
            </tr>
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
                <td>
                    @if(file_exists($approve->employee?->media->where('collection_name','signature')->first()?->getPath()))
                        <img src="{{$approve->employee->media->where('collection_name','signature')->first()->getPath()}}" style="width: 100px;height: 60px" alt="">
                    @endif
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>

    <div class="footer">
        <p></p>
        <p>Security Officer - {{$company?->title_security}}</p>
    </div>

</div>
</body>
