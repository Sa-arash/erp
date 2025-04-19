@include('pdf.header', [
    'titles' => ['Tasks List'],
    'css' => true,
    'title' => 'Tasks'
])

<style>
    .container {
        width: 210mm;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
    }
    .header {
        text-align: center;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .header h1 {
        margin: 0;
        font-size: 16px;
    }
    .header p {
        margin: 0;
        font-size: 12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 11px;
    }
    table, th, td {
        border: 1px solid #000;
    }
    th, td {
        padding: 6px;
        text-align: center;
    }
    th {
        background-color: #474646;
        color: #fff;
    }
    .footer {
        margin-top: 30px;
        font-size: 12px;
        text-align: center;
    }
</style>

<body>
<div class="container">
    <div class="header">
        <h1>Tasks Report</h1>
        <p>{{ now()->format('Y-m-d') }}</p>
    </div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Created By</th>
            <th>Recently Assigned/ Today</th>
            <th>Start Date</th>
            <th>Start Task</th>
            <th>Assigned Date</th>
            <th>End Task</th>
            <th>Due Date</th>
            <th>Priority</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($tasks as $index => $task)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $task->employee->name ?? '—' }}</td>
                <td>{{ $task->title }}</td>
                <td>{{ $task->start_date }}</td>
                <td>{{ $task->deadline }}</td>
                <td>{{ $task->created_at }}</td>
                <td>{{ optional($task->start_task)->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ optional($task->end_task)->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $task->priority_level }}</td>
                <td>{{ $task->status }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>


</div>
</body>
</html>
