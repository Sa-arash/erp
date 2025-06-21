@include('pdf.header',
  ['titles'=>[''],
  'css'=>true,'title'=>'Check IN and Check OUT'
  ])

<style>
    table{
        margin: 0;
    }
</style>
@php
    $entryData = $requestVisit->entry_and_exit;
    $i=1;
@endphp

@foreach($entryData as $date => $groups)
    <p>Date: {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}  (Visitors and Drivers) </p>
    @foreach(['visitors' => 'Visitors', 'drivers' => 'Drivers'] as $type => $label)
        @if(isset($groups[$type]))
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
