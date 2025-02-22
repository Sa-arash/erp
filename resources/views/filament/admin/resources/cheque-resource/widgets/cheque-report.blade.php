<x-filament-widgets::widget>
    <x-filament::section>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Date
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Total Receivable
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Total Payable
                    </th>

                </tr>
                @php
                    $fillerArray=['Today','In Week','In Month','In Three Month ','In Six Month ','In Twelve Month '];
                    $arrTime=[
                        0=>[\Carbon\Carbon::now()->startOfDay(),\Carbon\Carbon::Today()->endOfDay()],
                        1=>[\Carbon\Carbon::now()->startOf(\Carbon\Unit::Week)->startOfDay(),\Carbon\Carbon::now()->endOfWeek()->endOfDay()],
                        2=>[\Carbon\Carbon::now()->startOfMonth()->startOfDay(),\Carbon\Carbon::now()->endOfMonth()->endOfDay()],
                        3=>[\Carbon\Carbon::now()->startOfMonth()->startOfDay(),\Carbon\Carbon::now()->addWeeks(3)->endOfDay()],
                        4=>[\Carbon\Carbon::now()->startOfMonth()->startOfDay(),\Carbon\Carbon::now()->addWeeks(6)->startOfMonth()],
                        5=>[\Carbon\Carbon::now()->startOfMonth()->startOfDay(),\Carbon\Carbon::now()->addWeeks(12)->startOfMonth()],
                ];




                @endphp
                </thead>
                <tbody>

                @foreach($fillerArray as $key=> $filter)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{$filter}}
                        </th>
                        <td class="px-6 py-4">
                            {{number_format(\App\Models\Cheque::query()->whereBetween('due_date',[$arrTime[$key][0],$arrTime[$key][1]])->where('type',0)->sum('amount'))}}
                        </td>
                        <td class="px-6 py-4">
                            {{ number_format(\App\Models\Cheque::query()->whereBetween('due_date',[$arrTime[$key][0],$arrTime[$key][1]])->where('type',1)->sum('amount')) }}
                        </td>

                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
