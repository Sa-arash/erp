{{--  @dd($employee)  --}}
@include('pdf.header', [
    'css'=>false,
    'company'=>$employee->company,
    'titles'=>[],
    'title'=>'Journal Report'
    ]
    )

<style>
    body{
        font-family: Arial, sans-serif;
    }
    h1 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 28px;
        color: #2c3e50;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #ffffff;
        border: 1px solid #e3dddd;
        border-radius: 8px;
        overflow: hidden;
    }

    table th, table td {
        padding: 12px;
        text-align: left;
        vertical-align: top;
        font-size: 12px;
    }

    th {
        background-color: #ffffff;
        color: #333;
        font-weight: bold;
    }

    td {
        background-color: #ffffff;
    }

    .section-title {
        background-color: #f2f2f2;
        color: #555;
        font-weight: bold;
        padding: 12px;
        text-align: left;
        margin-top: 20px;
        border-radius: 5px;
        font-size: 15px;
        border: 1px solid #dcdcdc;
    }

    .empty {
        border: none;
        background-color: #f9f9f9;
        height: 20px;
    }

    @media print {
        body {
            background: white;
            color: black;
        }

        th {
            background-color: #ffffff !important;
            color: #333 !important;
            -webkit-print-color-adjust: exact;
        }

        .section-title {
            background-color: #e8e8e8 !important;
            color: #555 !important;
            -webkit-print-color-adjust: exact;
        }

        table {
            box-shadow: none;
        }
    }
</style>
<body>

<div style="text-align: center">
    <b>Employee Information Form</b>
</div>
<div>
    @if($employee->media->where('collection_name','images')->first())
        <img width="100" src="{{$employee->media->where('collection_name','images')->first()?->getPath()}}" alt="">

    @endif
</div>
<div class="section-title">Personal Information</div>
<table>
    <tr >
        <th style="border: 2px solid black !important;">Full Name:   {{$employee->fullName}}</th>
        <td></td>
        <th>NIC : {{$employee->NIC}}</th>
        <td></td>

    </tr>

    <tr>
        <th>
            Birth Date:
            @if($employee->birthday)
                {{ \Carbon\Carbon::create($employee->birthday)->format('Y/m/d') }}
                ( {{ \Carbon\Carbon::create($employee->birthday)->age }} )
            @else
                N/A
            @endif
        </th>
        <th></th>
        <th>Blood Group : {{$employee->blood_group}}</th>
        <td></td>
    </tr>
    <tr>
        <th>Phone :{{$employee->phone_number}}  </th>
        <td></td>
        <th>Zip/Postal Code : {{$employee->post_code}}</th>
        <td></td>
    </tr>
    <tr>
        <th>Country/State/City  {{ $employee->country."  ".$employee->state."  ".$employee->city }}</th>
        <td></td>
        <th>Address:  {{$employee->address }}</th>
        <td></td>
    </tr>
    <tr>
        <th>Marital Status : {{$employee->marriage}}</th>
        <td></td>
        <th>Gender : {{getGender($employee)}}</th>
        <td></td>
    </tr>
</table>

<div class="section-title">Job Information</div>
<table>
    <tr>

        <th>Employee ID Number : {{$employee->ID_number}}</th>
        <td></td>
    </tr>
    <tr>
        <th>Department : {{$employee->department->title}}</th>
        <td></td>
        <th>Designation : {{$employee->position->title}}</th>
        <td></td>
    </tr>
    <tr>
        <th>Duty Type : {{$employee->duty->title}}</th>
        <td></td>
        <th>Pay Frequency : {{$employee->contract->title}}</th>
        <td></td>
    </tr>
    <tr>
        <th>Work Phone</th>
        <td></td>
        <th>Start Date : {{\Carbon\Carbon::make($employee->joining_date)->format('Y/m/d')}}</th>
        <td></td>
    </tr>
    <tr>
        <th>Base Salary:   {{number_format($employee->base_salary)}}</th>
        <td></td>
        <th>Daily Salary : {{number_format($employee->daily_salary)}}</th>
        <td></td>
    </tr>
</table>

<div class="section-title">Emergency Contact Information</div>
<table>
    @if($employee->emergency_contact)
    @foreach($employee->emergency_contact as $emergency)
    <tr>
        <th>Name : {{$emergency['name'] }}</th>
        <th>Relation : {{$emergency['relation']}} </th>
        <th>Number : {{$emergency['number']}}</th>
        <th>Email : {{$emergency['email']}}</th>
        <td></td>
    </tr>
    @endforeach
    @endif
</table>
<div style="text-align: center">
    <p>    Employee Signature</p>
    @if($employee->media->where('collection_name','signature')->first())
    <img width="60" height="60" src="{{$employee->media->where('collection_name','signature')->first()?->getPath()}}" alt="">
    @endif
</div>
</body>

</html>
