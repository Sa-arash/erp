{{-- <!doctype html>
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
</body> --}}


@include('pdf.header',['title'=>'Gate Pass','titles'=>[],'css'=>false])
<style>
body {
font-family: Arial, sans-serif;
font-size: 8pt;
box-sizing: border-box;
}

table {
    width: 100%;
    border-collapse: collapse;
}
td{
    padding: 5px;
}

/* Header Section */
.header-section-table {
    margin-bottom: 25px; /* Space after header */
}

.header-section-table td {
    padding: 0;
    vertical-align: top;
}

.logo-cell {
    width: 60%; /* Adjusted width for logo side based on visual */
}

.logo {
    width: 150px; /* Approximate width from image */
    height: auto;
    display: block;
    padding-top: 10px; /* Space from top of page */
}

.date-cell {
    width: 40%; /* Adjusted width for date side */
    text-align: right;
    padding-top: 20px; /* Align date with general top of page content */
    font-size: 10pt;
}

.gate-pass-title {
    font-size: 16pt;
    font-weight: bold;
    padding-bottom: 5px;
    border-bottom: 2px solid black; /* Thicker line under Gate Pass */
    margin-bottom: 20px;
    padding-top: 5px; /* Adjust vertical position */
}

/* Main Info Table (From, To, Name, etc.) */
.main-info-table {
    margin-bottom: 20px;
}

.main-info-table td {
    border: 1px solid black;
    padding: 6px 8px; /* Consistent padding for cells */
    vertical-align: middle;
    height: 20px; /* Ensure consistent height for rows */
}





.main-info-table u {
    text-decoration: underline;
}

/* Item List Section */
.item-list-prompt {
    margin-bottom: 10px;
    font-size: 10pt;
    font-weight: bold;
}

.item-list-table {
    margin-bottom: 5px;
}

.item-list-table th,
.item-list-table td {
    border: 1px solid black;
    padding: 4px; /* Consistent padding for cells */
    text-align: left;
    vertical-align: middle;
    height: 20px; /* Consistent height for data rows */
}


/* Reason for Taking out */
.reason-field {
    margin-top: 15px;
    font-size: 10pt;
    padding-bottom: 5px; /* Space for the underline */
    border-bottom: 1px solid black; /* Line under the reason */
    margin-bottom: 25px; /* Space after this section */
    font-weight: bold;
}

.reason-field span {
    font-weight: normal; /* "GIFT" is not bold */
}

/* Status and Check Table */
.status-check-table {
    margin-bottom: 50px; /* Space before the final signature */
}

.status-check-table td {
    border: 1px solid black;
    padding: 4px !important;
    vertical-align: middle;
    height: 20px; /* Consistent height for all rows */
}

.status-check-table td:nth-child(1) { /* Status column */
    width: 30%;
    font-weight: bold;
}

.status-check-table td:nth-child(2) { /* Check one column */
    width: 10%;
    text-align: center;
}

.status-check-table td:nth-child(3) { /* Right column (Checked by, Verified by, Names, Signatures) */
    width: 60%;
    font-weight: bold; /* Labels like "Checked by" are bold */
}

.status-check-table td.signature-label-normal,
.status-check-table td.name-label-normal {
    font-weight: normal; /* Name and Signature labels are not bold */
}

.status-check-table td.dashed-line {
    font-weight: normal;
    text-align: center;
}

/* Footer Signature */
.security-signature-container {
    width: 100%;
    text-align: right; /* Aligns the table to the right */
    margin-top: 5px; /* Space from above content */
}

.security-signature-table {
    width: 280px; /* Fixed width to match the image's line length */
    margin-left: auto; /* Push to the right */
    border: none;
}

.security-signature-table td {
    border: none;
    padding: 0;
    text-align: center;
}

.security-signature-line {
    border-bottom: 1px solid black;
    height: 1px; /* Ensure the line is visible */
    width: 100%;
    margin-bottom: 2px; /* Space between line and text */
    display: block; /* Ensures border-bottom works correctly */
}

.security-signature-text {
    font-weight: bold;
    font-size: 10pt;
}

.security-signature-text u {
    text-decoration: underline;
}

@page {
    margin-left: 40px;
    margin-right: 40px;
}
.item-list-table td,th{
    padding: 2px !important;


}
table{
    margin: 0;
}

</style>


<body>

<p style="text-align: left;margin-bottom: 4px;margin-top: 4px;font-weight: bold">  Date: {{ \Illuminate\Support\Carbon::create($takeOut->date)->format('d F Y') }}</p>
<p style="text-align: left;margin-bottom: 4px;font-weight: bold">  Gate Pass No: {{$takeOut->number}} </p>
<table class="main-info-table">
    <tr>
        <td><b>From:</b> {{$takeOut->from}}</td>
        <td><b> To:</b> {{$takeOut->to}}</td>
    </tr>
    <tr>
        <td><b>Name:</b> {{$takeOut->employee->fullName}}</td>
        <td><b>Badge Number:</b> {{$takeOut->employee->ID_number}}</td>
    </tr>
    <tr>
        <td><b>Designation:</b> {{$takeOut->employee->position?->title}}</td>
        <td><b> Department: </b>{{$takeOut->employee->department?->title}}</td>
    </tr>
</table>

<p class="item-list-prompt" >Please allow the following items/materials out.</p>

<table class="item-list-table">
    <thead>
    <tr>
        <th style="text-align: center">SN</th>
        <th style="text-align: center">Item Description</th>
        <th style="text-align: center">Quantity</th>
        <th style="text-align: center">Unit</th>
        <th style="text-align: center">Remarks</th>
    </tr>
    </thead>
    <tbody>
    @php
        $i=1;
    @endphp
    @foreach ($takeOut->items as $item)
        {{-- @dd($item->asset); --}}
        <tr>
            <td style="text-align: center">{{$i++}}</td>
            <td style="text-align: center">{{$item->asset->title}}</td>
            <td style="text-align: center">1</td>
            <td style="text-align: center">Each</td>
            <td style="text-align: center">{{$item->remarks}}</td>
        </tr>

    @endforeach

    @foreach ($takeOut->itemsOut  as $itemOut)
        {{-- @dd($itemOut ); --}}
        <tr>
            <td style="text-align: center">{{$i++}}</td>
            <td style="text-align: center">{{ $itemOut["name"] }}</td>
            <td style="text-align: center">{{ $itemOut["quantity"] }}</td>
            <td style="text-align: center">{{ $itemOut["unit"] }}</td>
            <td style="text-align: center">{{$itemOut["remarks"]}}</td>
        </tr>

    @endforeach
    </tbody>
</table>

<div style="margin-bottom: 9px;margin-top: 9px"><b>Reason for Taking out:</b> {{$takeOut->reason}}</div>

<table class="status-check-table">
    <tr>
        <td><b>Status</b></td>
        <td><b>Check one</b></td>
        <td style="border: 1px solid #7a7272 ;"><b>Prepare By:</b></td>
    </tr>
    <tr>
        <td>Returnable</td>

        <td>{{$takeOut->status == "Returnable" ? '✓':''}}</td>
        <td style="border: 1px solid #7a7272 ;" >
            {{$takeOut->employee->fullName}}
        </td>

    </tr>
    <tr>
        <td>Non-Returnable</td>
        <td>{{$takeOut->status == "Non-Returnable" ? '✓':''}}</td>
        <td>{{$takeOut->employee->position->title}}</td>


    </tr>
    <tr>
        <td>Modification</td>
        <td>{{$takeOut->type == "Modification" ? '✓':''}}</td>
        <td rowspan="4" style="border: 1px solid #7a7272 ;" class="signature-label-normal">
            <div style="border-bottom: 1px solid black">

                Signature:
                @if (file_exists($takeOut->employee->media->where('collection_name','signature')->first()?->getPath())  )
                    <img
                        src="{!! $takeOut->employee->media->where('collection_name','signature')->first()->getPath() !!}"
                        style="margin-left: 50px;border-radius: 50px ; width: 80px;" alt="">
                    <br>
                    <p style="border: 0">
                        Date : {{ \Illuminate\Support\Carbon::create($takeOut->created_at)->format('d/F/Y h:i A') }}
                    </p>

                @endif
            </div>
        </td>
    </tr>
    <tr>
        <td>Personal Belonging</td>
        <td>{{$takeOut->type == "Personal Belonging" ? '✓':''}}</td>

    </tr>
    <tr>
        <td>Domestic Waste</td>
        <td>{{$takeOut->type == "Domestic Waste" ? '✓':''}}</td>

    </tr>
    <tr>
        <td>Construction Waste</td>
        <td>{{$takeOut->type == "Construction Waste" ? '✓':''}}</td>
    </tr>

</table>

<div class="security-signature-container">
    <table class="security-signature-table">
        <tr>
            <td>
                <span class="security-signature-line"></span>
                <p style="border-bottom: 2px solid black">Security Clearance</p>
                <br>
                @if(isset($takeOut->approvals[0]) and $takeOut->approvals[0]->status->value==="Approve")

                    @if (file_exists($takeOut->approvals[0]->employee->media->where('collection_name','signature')->first()?->getPath())  )
                        <img
                            src="{!! $takeOut->approvals[0]->employee->media->where('collection_name','signature')->first()->getPath() !!}"
                            style="  width: 80px;" alt="">
                    @endif
                    <br>
                    Digitally Signed By: {{$takeOut->approvals[0]->employee->fullName}}
                    <br>
                    Date: {{isset($takeOut->approvals[0]->approve_date)? \Illuminate\Support\Carbon::make($takeOut->approvals[0]->approve_date)->format('d/F/Y h:i A'):''}} +04:30
                @endif

                <div class="security-signature-text">Head of Security - <u>UNC</u></div>

            </td>
        </tr>
    </table>
</div>
</body>



