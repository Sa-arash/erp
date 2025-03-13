@include('pdf.header', ['titles' => ['Staff Clearance/Separation Form'], 'css'=>false] )


    <style>
        body {
            font-family: Vazir, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }
        .signature {
            margin-top: 40px;
        }
    </style>
</head>
<body>


<table>
    <tr>
        <th colspan="2">For employee use only:</th>
    </tr>
    <tr>
        <td>Name: {{$employee->fullName}}</td>
        <td>Position: {{$employee?->position?->title}}</td>
    </tr>
    <tr>
        <td>Duty Station: {{$employee->structure?->title}}</td>
        <td>Date: {{\Carbon\Carbon::make($employee->separation->date)->format('Y/m/d')}}</td>
    </tr>
    <tr>
        <td >Signature: (employee)</td>
        <td >
            @if($approve->employee->media->where('collection_name','signature')->first()?->original_url)
            <img src="{!! $employee->media->where('collection_name','signature')->first()->getPath() !!}" style="border-radius: 50px ; width: 80px;">
            @endif
        </td>
    </tr>
</table>

<div class="section-title">Departments</div>
<table>
    <tr>
        <th>Department</th>
        <th>Comments/Liabilities</th>
        <th>Signature</th>
    </tr>
    @foreach($employee->separation->approvals->where('status','Approve') as $approve)

        <tr>
            <td>{{$approve->employee?->department?->title}}</td>
            <td>{{$approve->comment}}</td>
            <td>
                @if($approve->employee?->signature_pic)
                <img src="{{public_path('images/'.$approve->employee?->signature_pic)}}" style="border-radius: 50px ; width: 80px;">
                @endif
            </td>
        </tr>
    @endforeach


</table>

<div class="section-title">Final Steps:</div>
<table>
    <tr>
        <td>Attested by: (Head of Department name and signature)</td>
        <td>Approved by: (Operations, name and signature)</td>
    </tr>
</table>

</body>
</html>
