
@include('pdf.header', [
    'titles' => ['Overtime Slip Form'],
    'title' => 'Overtime Slip Form',
    'css' => false,
])

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
  {{-- <div class="header">
    <div>
      <img src="https://i.imgur.com/EmcA5lE.png" alt="ATGT Logo">
      <div style="font-size: 12px;">AREA TARGET GENERAL TRADING L.L.C<br>ايريا تارقت للتجارة العامة ذ.م.م</div>
    </div>
    <div class="title">Overtime Slip Form</div>
  </div> --}}
{{-- @dd($overtime->employee->media->where('collection_name', 'signature')) --}}
  <table class="container">
    <tr>
      <td class="left-text-vertical" rowspan="4">Administrative Department</td>
      <td>Date: {{\Carbon\Carbon::parse($overtime->overtime_date)->format('d / M / Y')}}</td>
      <td>Department:{{$overtime->employee->department->title}}</td>
    </tr>
    <tr>
      <td>Time:{{$overtime->hours}}Hours</td>
      <td>Position:{{$overtime->employee->position->title}}</td>
    </tr>
    <tr>
      <td>Employee Name:{{$overtime->employee->fullName}}</td>
      <td>Employee Badge number:{{$overtime->employee->number}}</td>
    </tr>
    <tr>
      <td colspan="2" style="height: 80px;">Reason for Overtime:{{$overtime->title}}</td>
    </tr>
    <tr>
      <td colspan="2">Employee Signature:
        @if ($overtime->employee->media->where('collection_name', 'signature')->first())
                        <img width="60" height="60"
                            src="{{ $overtime->employee->media->where('collection_name', 'signature')->first()?->getPath() }}">
                    @endif
      </td>
      <td>Line Manager Signature:
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
