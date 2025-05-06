    {{-- <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;

        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .section-title {
            font-size: 12px;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        @page  {
            margin: 10px;
        }
    </style>
</head>
<body>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0;">
    <table >
        <tr >
            <td style="border: none;width: 20%; text-align: left; padding-left: 10px;">
                @if ($company?->logo_security)
                    <img src="{!! public_path('images/' . $company?->logo_security) !!}" style="padding: 0; border-radius: 50px ; width: 100px;">
                @endif
            </td>
            <td  style="border: none;text-align: center; vertical-align: middle; width: 40%;">
                <h4 style="margin: 0; padding: 0; font-size: 18px; white-space: nowrap; display: inline-block;">
                    {{ $company?->title_security }}
                </h4>
            </td>
            <td style="border: none;width: 20%; text-align: right; padding-right: 10px;">
                @if ($company?->logo_security)
                    <img src="{!! public_path('images/' . $company?->logo_security) !!}" style="padding: 0; border-radius: 50px ; width: 100px;">
                @endif
            </td>
        </tr>
    </table>

    <div class="">
        <div class="section">
            <div class="section-title">Requester’s Details</div>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>Agency</th>
                    <th>Cell Phone</th>
                    <th>Email</th>
                </tr>
                <tr>
                    <td>{{$requestVisit->employee->fullName}}</td>
                    <td>{{$requestVisit->agency}}</td>
                    <td>{{$requestVisit->employee->phone_number}}</td>
                    <td>{{$requestVisit->employee->email}}</td>
                </tr>

            </table>
        </div>

        <div class="section">
            <div class="section-title">Specific Visit Details</div>
            <table>
                <tr>
                    <th>Date of Visit</th>
                    <th>Time of Arrival</th>
                    <th>Time of Departure</th>
                    <th>Purpose of Visit</th>
                </tr>
                <tr>
                    <td>{{\Illuminate\Support\Carbon::create($requestVisit->visit_date)->format('Y/m/d')}}</td>
                    <td>{{$requestVisit->arrival_time}}</td>
                    <td>{{$requestVisit->departure_time}}</td>
                    <td>{{$requestVisit->purpose}}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Visitor(s) Details</div>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>ID/Passport</th>
                    <th>Cell Phone</th>
                    <th>Type</th>
                    <th>Organization</th>
                    <th>Remarks</th>
                </tr>
                @foreach ($requestVisit->visitors_detail as $visitor)
                
                    <tr>
                        <td>{{$visitor['name']??'---'}}</td>
                        <td>{{$visitor['id'??'---']}}</td>
                        <td>{{$visitor['phone']??'---'}}</td>
                        <td>{{$visitor['type']??'---'}}</td>
                        <td>{{$visitor['organization']??'---'}}</td>
                        <td>{{$visitor['remarks']??'---'}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="section">
            <div class="section-title">

            </div>
            <table>
                <tr>

                    <th colspan="5" style="text-align:center">Armed Close Protection Officers (If Applicable)</th>
                </tr>
                <tr>

                    <th>Type</th>
                    <th>National</th>
                    <th>International</th>
                    <th>De-facto Security Forces</th>
                    <th>Total</th>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>

            </table>
        </div>









        <div class="section">
            <div class="section-title">Driver and Vehicle Details</div>
            <table style="">
                <tr>
                    <th>Driver</th>
                    <th>Vehicle</th>
                </tr>
                <tr style="padding: 0px 0px ;margin:0px">
                    <td style="padding: 0px 0px ;margin:0px">
                        <table>
                            <tr>
                                <th>Full Name</th>
                                <th>ID/Passport</th>
                                <th>Cell Phone</th>
                            </tr>
                            @foreach ($requestVisit->driver_vehicle_detail as $driver)
                                <tr>
                                    <td>{{$driver['name']??'---'}}</td>
                                    <td>{{$driver['id']??'---'}}</td>
                                    <td>{{$driver['phone']??'---'}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                    <td style="padding: 0px 0px;margin :0px">
                        <table>
                            <tr>
                                <th>Model</th>
                                <th>Color</th>
                                <th>Registration Plate</th>
                            </tr>
                            @foreach ($requestVisit->driver_vehicle_detail as $vehicle)
                                <tr>
                                    <td>{{$vehicle['model']??'---'}}</td>
                                    <td>{{$vehicle['color']??'---'}}</td>
                                    <td>{{$vehicle['Registration_Plate']??'---'}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>


            </table>
        </div>
        <div class="section">
            <div class="section-title">Approval</div>


            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="white-space: nowrap; width: 1%;">FSU UNHCR</td>
                    <td style="width: auto;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="width: auto;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="width: auto;">Date : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; width: 1%;">FSA FAO</td>
                    <td style="width: auto;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="width: auto;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="width: auto;">Date : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; width: 1%;">ICON SFP</td>
                    <td style="width: auto;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="width: auto;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="width: auto;">Date : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
            </table>
            @if (isset($requestVisit->approvals[0]))
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Approval Date</th>
                        <th>Signature</th>
                    </tr>
                    </thead>
                    @foreach ($requestVisit->approvals as $approve)
                        <tr>
                            <th>{{$approve->employee->fullName}}</th>
                            <th>{{$approve->position}}</th>
                            <th>{{$approve->status}}</th>
                            <th>{{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('M j, Y / h:iA'):""}}</th>
                            <th>

                                @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                                    <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 70px;height: 30px" alt="">
                                @endif
                            </th>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>
    </div>
</div>
</body>
</html>
--}}


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Visitor Access Request Form</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
                margin: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td,
            th {
                border: 1px solid #000;
                padding: 4px;
                vertical-align: top;
            }

            .header {
                text-align: center;
                font-weight: bold;
            }

            .sub-header {
                text-align: center;
                font-size: 12px;
            }

            .section-title {
                background-color: #dbe5f1;
                font-weight: bold;
                text-align: center;
            }

            .section-title td {
                text-align: center;
                font-weight: bold;
            }

            .text-center {
                text-align: center;
            }

            .no-border {
                border: none;
            }

            .small-text {
                font-size: 12px;
            }

            .approved-box,
            .not-approved-box {
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                display: inline-block;
                margin-right: 5px;
            }

            .light-blue {
                background-color: #dbe5f1;
            }

            .gray-bg {
                background-color: #f2f2f2;
            }

            .center {
                text-align: center;
            }

            .approved-box,
            .not-approved-box {
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                display: inline-block;
                margin-right: 5px;
            }

            .bg-lightgreen {
                background-color: rgb(197, 224, 179, 255) !important;
            }

            @media print {
                body {
                    margin: 0;
                }
            }



            .equal-table {
                width: 100%;
                /* عرض جدول را به 100% تنظیم کنید */
                border-collapse: collapse;
                /* برای حذف فاصله بین سلول‌ها */
            }

            .equal-cell {
                width: 25%;
                /* عرض هر ستون را به 25% تنظیم کنید */
                border: 1px solid #000;
                /* برای نمایش مرز سلول‌ها */
            }
        </style>
    </head>

    <body>

        <table>
            <tr>
                <td colspan="2">
                    @if ($company?->logo_security)
                        <img src="{!! public_path('images/' . $company?->logo_security) !!}" style="padding: 0; border-radius: 50px ; width: 200px;">
                    @endif
                </td>
                <td colspan="4" class="header">
                    HART<br>
                    UNHCR Guard Force Unit, Kabul, Afghanistan<br>
                    <span class="small-text">SOP No.002 Annex {{ str_pad($requestVisit->id, 3, '0', STR_PAD_LEFT) }}
                        dated {{ $requestVisit->visit_date }}<br>
                        Supersedes: Visitors Access Request {{ str_pad($requestVisit->id, 3, '0', STR_PAD_LEFT) }} Dated
                        dated {{ $requestVisit->visit_date }}<br>
                        Effective Date: {{ $requestVisit->visit_date }}</span>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="6" class="header">ICON COMPOUND - VISITOR ACCESS REQUEST FORM</td>
            </tr>
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="6">Requestor’s Details</td>
            </tr>

            <tr>
                <td>Requestor’s Name</td>
                <td>Title</td>
                <td>UN Agency</td>
                <td>Cell Phone</td>
                <td colspan="2">Email</td>
            </tr>

            <tr>
                <td> {{ $requestVisit->employee->fullName }}</td>
                <td> {{ $requestVisit->employee->agency }}</td>
                <td> {{ $requestVisit->agency }}</td>
                <td> {{ $requestVisit->employee->phone_number }}</td>
                <td colspan="2">{{ $requestVisit->employee->email }}</td>
            </tr>
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="3">Specific Visit Details</td>
            </tr>
            <tr>
                <td>Date of Visit</td>
                <td>Time of Arrival</td>
                <td>Time of Departure</td>
            </tr>
            <tr>
                <td>{{ \Illuminate\Support\Carbon::create($requestVisit->visit_date)->format('Y/m/d') }}</td>
                <td>{{ $requestVisit->arrival_time }}</td>
                <td>{{ $requestVisit->departure_time }}</td>
            </tr>
            <tr>
                <td>Purpose of visit (Be specific)</td>
                <td colspan="2">{{ $requestVisit->purpose }}</td>
            </tr>
            <tr>
                <td colspan="3" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="6">Visitor(s) Details</td>
            </tr>
            <tr>
                <td>Name</td>
                <td>ID/Passport</td>
                <td>Cell Phone</td>
                <td>Type</td>
                <td>Organization</td>
                <td>Remarks</td>
            </tr>
            @foreach ($requestVisit->visitors_detail as $visitor)
                <tr>
                    <td>{{ $visitor['name'] ?? '---' }}</td>
                    <td>{{ $visitor['id' ?? '---'] }}</td>
                    <td>{{ $visitor['phone'] ?? '---' }}</td>
                    <td>{{ $visitor['type'] ?? '---' }}</td>
                    <td>{{ $visitor['organization'] ?? '---' }}</td>
                    <td>{{ $visitor['remarks'] ?? '---' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="6">Armed Close Protection Officers (If Applicable)</td>
            </tr>
            <tr>
                <td><strong>Type</strong></td>
                <td>□ National</td>
                <td>□ International</td>
                <td colspan="3">□ De-facto Security Forces</td>

            </tr>
            <tr>
                <td><strong>Total</strong></td>

                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td class="text-center" colspan="6">Driver(s)/Vehicle(s) Details</td>
            </tr>
            <tr>
                <td class="text-center" colspan="3"><strong>Driver’s Details</strong></td>
                <td class="text-center" colspan="3"><strong>Vehicle Details</strong></td>
            </tr>




            <tr>
                <td>Full Name</td>
                <td>ID Type & No.</td>
                <td>Cell Phone</td>
                <td>Type/Model</td>
                <td>Color</td>
                <td>Registration Plate</td>
            </tr>
            {{-- @dd($requestVisit) --}}
            @foreach ($requestVisit->driver_vehicle_detail as $driver)
                <tr>
                    <td>{{ $driver['name'] ?? '---' }}</td>
                    <td>{{ $driver['id'] ?? '---' }}</td>
                    <td>{{ $driver['phone'] ?? '---' }}</td>
                    <td>{{ $vehicle['model'] ?? '---' }}</td>
                    <td>{{ $vehicle['color'] ?? '---' }}</td>
                    <td>{{ $vehicle['Registration_Plate'] ?? '---' }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="no-border">Requestor’s Signature:
                </td>

                <td class="no-border">
                    <img src="{{ $requestVisit->employee->media->where('collection_name', 'signature')->first()->getPath() }}"
                        style="width: 120px;height: 70px" alt="">
                </td>

                <td class="no-border">Date:</td>
                <td class="no-border">
                    {{ \Illuminate\Support\Carbon::create($requestVisit->visit_date)->format('Y/m/d') }}</td>
            </tr>

        </table>



        @if (isset($requestVisit->approvals[0]))
            <table class="equal-table ">
                <tr class="section-title bg-lightgreen">
                    <td colspan="4" class="equal-cell bg-lightgreen">Endorsement and Approval</td>
                </tr>
                <tr>
                    <td class="equal-cell">FSU UNHCR</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell">Date:</td>
                </tr>
                @foreach ($requestVisit->approvals as $approve)
                <tr>
                    <td class="equal-cell">FSA FAO</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell" rowspan="2">@if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                        <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 120px;height: 70px" alt="">
                    @endif</td>
                    <td class="equal-cell">Date:
                        
                    </td>
                </tr>
                    <tr>
                        <td class="equal-cell">ICON SFP</td>
                        <td class="equal-cell">{{$approve->employee->fullName}}</td>
                      
                        <td class="equal-cell">Date:
                            {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('Y.m.d H:i:s') : "" }} +04'30
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="equal-cell">Remarks to CSM</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell">□ Approved</td>
                    <td class="equal-cell">□ Not Approved</td>
                </tr>
                <tr>
                    <td colspan="4" class="no-border" style="text-align: right;">&nbsp;</td>
                </tr>

            </table>
        @endif














        {{-- 
            @if (isset($requestVisit->approvals[0]))
            <table>
                @foreach ($requestVisit->approvals as $approve)
                            {{-- <tr>
                                <th>{{$approve->employee->fullName}}</th>
                                <th>{{$approve->position}}</th>
                                <th>{{$approve->status}}</th>
                                <th>{{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('M j, Y / h:iA'):""}}</th>
                                <th>
    
                                    @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                                        <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 70px;height: 30px" alt="">
                                    @endif
                                </th>
                            </tr> 
                            
                            <tr>
                                <td class="no-border">Approved By:  </td>
                                <td class="no-border"> {{$approve->employee->fullName}}</td>
                                <td class="no-border">Signature: </td>
                                <td class="no-border">     @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                                    <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 100px;height: 40px" alt="">
                                @endif</td>
                                <td class="no-border">Date: </td>
                                <td class="no-border"> {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('M j, Y '):""}}</td>
                                <td class="no-border">Time: </td>
                                <td class="no-border"> {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format(' h:iA'):""}}</td>
                            </tr>
                        @endforeach
             
                <tr>
                    <td colspan="6" class="no-border" style="text-align: right;">   &nbsp;</td>
                </tr>
            </table>
            @endif --}}





    </body>

    </html>
