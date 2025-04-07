    <!doctype html>
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
                    {{-- @dd($visitor) --}}
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
            @if(isset($requestVisit->approvals[0]))
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
                    @foreach($requestVisit->approvals   as $approve)
                        <tr>
                            <th>{{$approve->employee->fullName}}</th>
                            <th>{{$approve->position}}</th>
                            <th>{{$approve->status}}</th>
                            <th>{{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('M j, Y / h:iA'):""}}</th>
                            <th>

                                @if($approve->employee->media->where('collection_name','signature')->first()?->original_url and $approve->status->name==="Approve")
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





