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
        font-size: 12px;
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
    .task-card {
        border: 1px solid #000;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
    }
    .task-header {
        font-weight: bold;
        background-color: #f1f1f1;
        padding: 5px;
        margin-bottom: 10px;
    }
    .task-body {
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }
    .task-col {
        width: 50%;
    }
    .task-row {
        margin: 4px 0;
    }
    .label {
        font-weight: bold;
        display: inline-block;
        width: 120px;
    }
</style>

<body>


<div class="container">
    <div class="header">
        <h1>Tasks Report</h1>
        <p>{{ now()->format('Y-m-d') }}</p>
    </div>

    @foreach($tasks as $index => $task)
        <div class="task-card">
            <div class="task-header">Task #{{ $index + 1 }}</div>
            <table class="task-table">
                <tr>
                    <td width="50%">
                        <div><span class="label">Created By:</span> {{ $task->employee->fullName ?? '—' }}</div>
                        <div><span class="label">Title:</span> {{ $task->title }}</div>
                        <div><span class="label">Start Date:</span> {{ $task->start_date }}</div>
                        <div><span class="label">Deadline:</span> {{ $task->deadline }}</div>
                        <div><span class="label">Assigned Date:</span> {{ $task->created_at }}</div>
                    </td>

                    <td width="50%">
                        <div><span class="label">Start Task:</span> {{ $task->start_task ?? '—' }}</div>
                        <div><span class="label">End Task:</span> {{ $task->end_task ?? '—' }}</div>
                        <div><span class="label">Priority:</span> {{ $task->priority_level }}</div>
                        <div><span class="label">Status:</span> {{ $task->status }}</div>
                        <div><span class="label">Employees:</span> {{ $task->employees->map(fn($employee) => $employee->fullName)->join(', ') }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">Detail:{{$task->description}}</td>
                </tr>
            </table>
        </div>
    @endforeach
</div>

</body>
</html>
