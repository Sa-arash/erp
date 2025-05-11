

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
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

    .form-section td {
      height: 60px;
    }
  </style>
<body>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0;">
    <table style="border: 1px solid black">
        <tr >
            <td style="border: none;width: 20%; text-align: left; padding-left: 10px;">
            </td>
            <td  style="border: none;text-align: center; vertical-align: middle; width: 40%;">

                <h4 style="margin: 0; padding: 0; font-size: 25px; white-space: nowrap; display: inline-block">
                    {{strtoupper('Overtime Slip Form')}}
                </h4>
            </td>
            <td style="border: none;width: 20%; text-align: right; padding-right: 10px;">
                @if($company?->logo)
                    <img src="{!! public_path('images/' . $company?->logo) !!}" style="padding: 0; border-radius: 50px ; width: 150px;">
                @endif
            </td>
        </tr>
    </table>
</div>
  <table class="container">
    <tr>
      <td style="border: none"  rowspan="5">
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
</body>
</html>
