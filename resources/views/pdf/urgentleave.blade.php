@include('pdf.header',['title'=>'Urgent Leave Slip','titles'=>[],'css'=>false])
  <style>
    body {
      font-family: Calibri, sans-serif;
      margin: 40px;
      color: #000;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header img {
      height: 60px;
    }
    .title {
      font-size: 20px;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    td {
      border: 1px solid #000;
      padding: 8px;
      vertical-align: top;
    }
    .side-header {
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      text-align: center;
      background-color: #e8f0ff;
      font-weight: bold;
      color: #004aad;
      font-size: 14px;
      border-right: none;
    }
    .content-table td:first-child {
      width: 30%;
      font-weight: bold;
    }
    .content-table td:nth-child(2) {
      width: 70%;
    }
  </style>

  <table>
     <tr>
         <td class="" rowspan="11   ">
             <img src="{{public_path('img/dep.png')}}"  alt="">
         </td>
      <tr><td><b>Date:</b></td><td colspan="2"> {{\Illuminate\Support\Carbon::make($urgent->date)->format('Y F d')}}</td></tr>
      <tr><td><b>Name:</b></td><td colspan="2">{{$urgent->employee->fullName}}</td></tr>
      <tr><td><b>Badge Number:</b></td><td colspan="2">{{$urgent->employee->ID_number}}</td></tr>
      <tr><td><b>Department:</b></td><td colspan="2">{{$urgent->employee?->department->title}}</td></tr>
      <tr><td><b>Reason:</b></td><td colspan="2">{{$urgent->reason}}</td></tr>
      <tr><td><b>Time Out:</b></td><td colspan="2">{{\Illuminate\Support\Carbon::make($urgent->time_out)->format('h:iA')}}</td></tr>
      <tr><td><b>Time In:</b></td><td colspan="2">
              @if( $urgent->time_in )
                  {{\Illuminate\Support\Carbon::make($urgent->time_in)->format('h:iA')}}
          @else
              NOT RETURNING TO DUTY
          @endif
          </td>
      </tr>
      <tr><td style="text-align: center"><b>Staff Signature:</b></td><td colspan="2">@if ($urgent->employee?->media->where('collection_name', 'signature')?->first())
                  <img width="60" height="60"
                       src="{{ $urgent->employee?->media->where('collection_name', 'signature')?->first()?->getPath() }}">
              @endif</td></tr>
      <tr><td style="text-align: center"><b>Approved by Line Manager:</b></td><td>
              {{$urgent->approvals->first()?->employee->fullName}}
          </td>
      <td style="border-right: 1px solid white!important;">
          @if ($urgent->approvals[0]->    employee?->media->where('collection_name', 'signature')?->first())
              <img width="60" height="60"
                   src="{{ $urgent->approvals[0]->employee?->media->where('collection_name', 'signature')?->first()?->getPath() }}">
          @endif
      </td></tr>
      <tr><td style="text-align: center"><b>Approved by HR Department:</b></td><td>{{$urgent->admin?->fullName}}</td>
      <td >
          @if ($urgent->admin?->media->where('collection_name', 'signature')?->first())
              <img style="text-align: end" width="60" height="60"
                   src="{{ $urgent->admin?->media->where('collection_name', 'signature')?->first()?->getPath() }}">
          @endif
      </td></tr>
  </table>
