@include('pdf.header', ['titles' => ['Visitor Request Details'], 'css'=>false] )
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
</style>
</head>
<body>
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
        <div class="section-title">Endorsement and Approval </div>
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
    </div>
    <div class="section">
        <div class="section-title">Approval</div>
        


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
                    <th>{{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('Y-m-d H:i'):""}}</th>
                    <th>
                        @if($approve->employee?->signature_pic and $approve->status==="Approve")
                        <img src="{{public_path('images/'.$approve->employee?->signature_pic)}}" style="width: 70px;height: 30px" alt="">
                        @endif
                    </th>
                </tr>
            @endforeach
        </table>
    </div>
</div>




