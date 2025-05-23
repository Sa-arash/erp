<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseRequest;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PurchasePrice extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'purchasePrice';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Company PurchasePrice';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $months = collect([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ]);

        $purchaseData = $months->map(function ($month, $index) {
            return PurchaseRequest::query()
                ->where('status', 'Finished')->get()
                ->filter(fn($request) => Carbon::parse($request->request_date)->month == $index + 1)
                ->sum(fn($request) => $request->bid?->total_cost ?? 0);
        })->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'PR Total Cost',
                    'data' => $purchaseData,
                ],
            ],
            'xaxis' => [
                'categories' => $months->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }
}
