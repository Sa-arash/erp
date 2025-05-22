
@include('pdf.header', [
    'titles' => [''],
    'css' => false,
    'title' => 'Visitor Access Requests'
])
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        @page  {
           margin: 5px;
        }
    </style>
</head>
<body>


<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Requester</th>
        <th>Visitors</th>
        <th>Visit Date</th>
        <th>Arrival</th>
        <th>Departure</th>
        <th>Track Time</th>
        <th>Status</th>
        <th>Gate Status</th>
        <th>Created At</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($requestVisits as $index => $record)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $record->employee->fullName ?? '---' }}</td>
            <td>
                {{ implode(', ', array_map(fn($item) => $item['name'], $record->visitors_detail ?? [])) }}
            </td>
            <td>{{ \Carbon\Carbon::parse($record->visit_date)->format('Y-m-d') }}</td>
            <td>{{ \Carbon\Carbon::parse($record->arrival_time)->format('H:i') }}</td>
            <td>{{ \Carbon\Carbon::parse($record->departure_time)->format('H:i') }}</td>
            <td>
                @php
                    $startTime = $record->InSide_date;
                    $endTime = $record->OutSide_date;

                @endphp
                {{ ($startTime && $endTime) ? calculateTime($startTime, $endTime) : '---' }}
            </td>
            <td>{{ ucfirst($record->status ?? '---') }}</td>
            <td>{{ $record->gate_status ?? '---' }}</td>
            <td>{{ \Carbon\Carbon::parse($record->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
