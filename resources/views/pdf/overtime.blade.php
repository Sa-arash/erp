@include('pdf.header',['title'=>'Overtime Slip Form','titles'=>[],'css'=>false])
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            width: 100%;
        }

        .container {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
        }

        .container td, .container th {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header img {
            height: 60px;
        }

        .header .title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
        }

        .left-text-vertical {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            background-color: #fff;
            color: #003399;
            font-weight: bold;
        }

        .form-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table{
            width: 100%;
        }

        .form-section td {
            height: 60px;
        }

    </style>




<table class="container">
    <tr>
        <td style="border: none"  rowspan="4">
            <img src="{{public_path('img/dep.png')}}"  alt="">
        </td>
        <td>
            <b>Date:</b> {{\Carbon\Carbon::parse($overtime->overtime_date)->format('d / M / Y')}}
            <br>
            <br>
            <b>Time:</b> {{$overtime->hours}} Hours
        </td>
        <td>
            <b>Department:</b> {{$overtime->employee->department->title}}<br>
            <br>
            <b>Position:</b> {{$overtime->employee->position->title}}

        </td>
    </tr>

    <tr>
        <td><b>Employee Name:</b> {{$overtime->title}}</td>
        <td><b>Employee Badge number:</b> {{$overtime->employee->ID_number}}</td>
    </tr>
    <tr>
        <td  colspan="2" style="height: 80px;"><b>Reason for Overtime:</b> {{$overtime->title}}</td>
    </tr>
    <tr>
        <td ><b>Employee Signature:</b>
            @if ($overtime->employee->media->where('collection_name', 'signature')->first())
                <img width="60" height="60"
                     src="{{ $overtime->employee->media->where('collection_name', 'signature')->first()?->getPath() }}">
            @endif
        </td>
        <td><b>Line Manager Signature:</b>
            @if (isset($overtime->approvals[0]))
                @if ($overtime->approvals[0]->employee->media->where('collection_name', 'signature')->first()?->original_url and $overtime->approvals[0]->status->name === 'Approve')
                    <img src="{{ $overtime->approvals[0]->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 120px;height: 70px" alt="">
                @endif
            @endif
        </td>
    </tr>
</table>


