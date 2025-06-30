@php
    use Illuminate\Support\Carbon;

    $employeeId = getEmployee()->id;
    $year = now()->year; // می‌تونی سال خاصی رو بزاری

    $months = collect(range(1, 12))->map(function ($month) use ($employeeId, $year) {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $leaves = \App\Models\Leave::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'accepted')
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('start_leave', [$monthStart, $monthEnd])
                      ->orWhereBetween('end_leave', [$monthStart, $monthEnd])
                      ->orWhere(function ($q) use ($monthStart, $monthEnd) {
                          $q->where('start_leave', '<=', $monthStart)
                            ->where('end_leave', '>=', $monthEnd);
                      });
            })
            ->get();

        return [
            'month' => $month,
            'leaves' => $leaves,
        ];
    });
@endphp

<table border="1" cellpadding="8" style="margin: auto; text-align: center;">
    <thead>
    <tr>
        @foreach ($months as $data)
            <th>{{ \Carbon\Carbon::create(null, $data['month'], 1)->format('M') }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    <tr>
        @foreach ($months as $data)
            <td>
                {{ $data['leaves']->sum('days') }}
            </td>
        @endforeach
    </tr>
    </tbody>
</table>

