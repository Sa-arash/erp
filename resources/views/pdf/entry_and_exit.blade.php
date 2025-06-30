@include('pdf.header',
  ['titles'=>[''],
  'css'=>true,'title'=>'Check IN & Check OUT'
  ])

<style>
    table{
        margin: 0;
    }
    @page  {
        margin-right: 35px;
        margin-left: 35px;
    }
</style>
@php
    $entryData = $requestVisit->entry_and_exit;
@endphp

@if(isset($entryData))
    @foreach($entryData as $date => $groups)
        <p style="font-weight: bold; margin-top: 30px;">
            Date: {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
        </p>

        @foreach(['visitors' => 'Visitors', 'drivers' => 'Drivers/Vehicles'] as $type => $label)
            @if(isset($groups[$type]))
                <h4 style="margin-bottom: 5px; margin-top: 15px;">{{ $label }}</h4>

                @php $i = 1; @endphp

                <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;" border="1">
                    <thead style="background:#f0f0f0;">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Check IN</th>
                        <th>Check OUT</th>
                        <th>Track Time</th>
                        <th>Comment IN</th>
                        <th>Comment OUT</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($groups[$type] as $name => $info)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $name }}</td>
                            <td>{{ $info['Check IN'] ?? '-' }}</td>
                            <td>{{ $info['Check OUT'] ?? '---' }}</td>
                            <td>{{ $info['Track Time'] ?? '----' }}</td>
                            <td>{{ $info['Comment IN'] ?? '-' }}</td>
                            <td>{{ $info['Comment OUT'] ?? ' ' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endforeach
@else
    <div style="text-align: center; margin-top: 30px; font-style: italic;">
        No Data
    </div>
@endif

