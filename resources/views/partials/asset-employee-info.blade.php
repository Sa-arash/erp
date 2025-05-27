<div class="space-y-4 ">
    <div style="display: flex">
        <div style="width: 50%">
            <span class="font-bold">Employee/Person Name:</span>
            {{ $record->employee->fullName }}
        </div>
        <div style="width: 50%">
            <span class="font-bold">Return Date:</span>
            {{ \Illuminate\Support\Carbon::make($record->date)->format('d/F/Y') }}
        </div>
    </div>
    <div>
        <span class="font-bold">Description:</span>
        {{ $record->description }}
    </div>

    <div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-800 ">
            <tr>
                <th colspan="3" class=" p-2 font-bold">Returned Assets</th>
            </tr>
            <tr>
                <th class="border p-2 text-left">Asset Title</th>
                <th class="border p-2 text-left">Location</th>
                <th class="border p-2 text-left">Address</th>
            </tr>
            </thead>
            <tbody>
            @foreach($record->assetEmployeeItem as $item)
                <tr>
                    <td class="border p-2">{{ $item->asset->titlen }}</td>
                    <td class="border p-2">{{ $item?->warehouse?->title }}</td>
                    <td class="border p-2">{{ $item?->structure?->title }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
