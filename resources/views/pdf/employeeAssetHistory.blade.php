<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{strtoupper($histories->employee_id? 'Employee Assets' : 'Personnel Assets')}}</title>
    <style>
        .asset-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: sans-serif;
            font-size: 14px;
        }

        .asset-table th,
        .asset-table td {
            border: 1px solid #ddd;

            padding: 8px;
            vertical-align: top;
        }

        .asset-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }

        .sub-table {
            width: 100%;
            border: none;
        }

        .sub-table td {
            border: none;
            padding: 4px 8px;
        }

        .row-highlight {
            background-color: #fafafa;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        table tbody tr td {
            background: white !important;

        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #333;
            font-size: 10px; /* Base font size */
        }

        .container {
            width: 210mm; /* A4 width */
            margin: 10mm auto; /* Reduced top/bottom margin */
            padding: 15px 25px; /* Reduced padding */
            border: 1px solid #eee;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.03);
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #0056b3;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #0056b3;
            text-transform: uppercase;
        }

        .header p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #555;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; /* Reduced margin */
        }

        table, th, td {
            border: 0; /* Lighter border for details tables */
        }

        th, td {
            padding: 5px 10px; /* Reduced padding */
            vertical-align: top;
            font-size: 10px; /* Reduced font size */
            text-align: left; /* Align text to left in cells */

        }

        th {
            background-color: #f9f9f9; /* Light background for labels */
            width: 30%; /* Allocate width for label column */
            font-weight: bold;
            color: #444;
        }


        @page  {
            margin-top: 25mm;  /* این خط خیلی مهمه: فضای کافی برای هدر */
            margin-left: 20px;
            margin-right: 20px;
            footer: MyFooter;
            header: MyHeader;

        }

    </style>
</head>
<body>
<htmlpageheader  name="MyHeader" >
    <div>
        <table style="border: 1px solid black;" >
            <tr >
                <td style="border: none;width: 20%; text-align: left; padding-left: 10px;">

                </td>
                <td  style="border: none;text-align: center; vertical-align: middle; width: 40%;">
                    <h4 style="margin: 0; padding: 0; font-size: 22px; white-space: nowrap; display: inline-block;">
                        {{strtoupper($histories->employee_id? 'Employee History' : 'Personnel History')}}
                    </h4>

                </td>

                <td style="border: none;width: 20%; text-align: right; padding-right: 10px;">
                    @if($company?->logo)
                        <img src="{!! public_path('images/' . $company?->logo) !!}" style="padding: 0; border-radius: 50px ; width: 100px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>
</htmlpageheader>


<table style="border: 2px solid black">

    @if($histories->employee_id)
        <tr>
            <!-- ستون اطلاعات کارمند -->
            <td style="width: 75%; vertical-align: top;">
                <p><b>Employee Name:</b> {{ $histories->employee->fullName }}</p>
                <p><b>Position:</b> {{ $histories->employee?->position?->title }}</p>
                <p><b>Badge Number:</b> {{ $histories->employee->ID_number }}</p>
                <p><b>Department:</b> {{ $histories->employee?->department?->title }}</p>
                <p><b>Location:</b> {{ $histories->employee?->warehouse?->title . ' ' . getParents($histories->employee?->structure) }}</p>
            </td>

            <!-- ستون تصویر کارمند -->
            <td style="width: 25%; text-align: right; vertical-align: top;">
                @if(file_exists($histories->employee->media->where('collection_name','images')->first()?->getPath()))
                    <img width="100"
                         src="{{ $histories->employee->media->where('collection_name','images')->first()?->getPath() }}"
                         alt="Employee Image">
                @endif
            </td>
        </tr>



    @else
        <tr>
            <td style="width: 75%; vertical-align: top;">
                <p><b>Personnel: </b>{{$histories->person->name}}</p>
                <p><b>Group: </b>{{$histories->person->person_group}}</p>
                <p><b>Badge Number:</b> {{ $histories->employee->ID_number }}</p>
                <p><b>Personnel Number: </b>{{$histories->person->number}}</p>
                <p><b>Job Title: </b>{{$histories->person->job_title}}</p>
            </td>

            <td style="width: 25%; text-align: right; vertical-align: top;">
                @if(file_exists($histories->person->media->where('collection_name','images')->first()?->getPath()))
                    <img width="100"
                         src="{{ $histories->person->media->where('collection_name','images')->first()?->getPath() }}"
                         alt="Employee Image">
                @endif
            </td>
        </tr>
    @endif
</table>

<table>

    @foreach($histories->assetEmployeeItem as $history)

        <tr>

            <td style="width: 80%;border: 2px solid black">
                <table style="width: 100%;">
                    <tr>
                        <td colspan="4" style="text-align: center;font-size: 16px;background-color: #ffffaa"><b>Asset Description</b></td>
                    </tr>

                    <tr>
                        <td><strong> Asset Specification</strong></td>
                        <td>{{$history->asset->description}}</td>
                        <td><strong>Asset Number:</strong></td>
                        <td>{{$history->asset->number}}</td>
                    </tr>
                    <tr>
                        <td><strong>Asset Type:</strong></td>
                        <td>{{$history->asset->type}}</td>
                        <td><strong>Brand:</strong></td>
                        <td>{{$history->asset->brand->title}}</td>
                    </tr>
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td>{{$history->warehouse?->title.' '.getParents($history->structure)}}</td>
                        <td><strong>Condition:</strong></td>
                        <td>{{$history->asset->quality}}</td>
                    </tr>
                    <tr>
                        <td><strong>Price:</strong></td>
                        <td>{{number_format($history->asset->price)}}</td>
                        <td><strong>PO No:</strong></td>
                        <td>{{$history->asset->po_number}}</td>
                    </tr>
                    <tr>
                        <td><strong>Note</strong></td>
                        <td colspan="3" class="notes">{{$history->asset->note}}</td>
                    </tr>

                    <tr>
                        <td colspan="4" style="text-align: center;font-size: 16px;background-color: #ffffaa"><b> History Overview</b></td>
                    </tr>
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td>{{$history->warehouse?->title.' '.getParents($history->structure)}}</td>
                        <td><strong>Type</strong></td>
                        <td>{{$history->type}}</td>
                    </tr>
                    <tr>
                        <td><strong>Assign / Return Date:</strong></td>
                        <td>{{\Illuminate\Support\Carbon::make($history->created_at)->format('d/M/Y h:i A')}}</td>
                        <td><strong>Due Date:</strong></td>
                        <td>{{$history->due_date ?  \Illuminate\Support\Carbon::make($history->due_date)->format('d/M/Y '):'' }}</td>
                    </tr>
                    <tr>
                        <td ><strong>Description : </strong></td>
                        <td  colspan="3">{{$history->description}}</td>
                    </tr>

                </table>
            </td>
            <td style="width: 10%;border: 2px solid black">
                <table>
                    <tr>
                        <td class="barcode" style="text-align: center">
                            {!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($history->asset->number, 'C39',1   ,20) .'" style="width:100px" alt="barcode"/>' !!}
                            <br>

                            {{$history->asset->number}}<br><br>
                            @if($history->asset->media->where('collection_name','images')->first())
                                <img class="asset-image" width="100"
                                     src="{{$history->asset->media->where('collection_name','images')->first()?->getPath()}}"
                                     alt="">
                            @endif
                        </td>

                    </tr>
                    <tr>

                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
</table>
<htmlpagefooter name="MyFooter">
    <div style="text-align: center; font-size: 10px;margin-top: 5px">
        Page {PAGENO} of {nbpg}
    </div>
</htmlpagefooter>
</body>
</html>







