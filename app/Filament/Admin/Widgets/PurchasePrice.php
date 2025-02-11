<?php

namespace App\Filament\Admin\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;

class PurchasePrice extends ApexChartWidget
{
    // use HasWidgetShield;

    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'assetInStorage';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Monthly PR Total Cost';

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
            return getCompany()->purchaseRequests
                ->where('status', 'Finished')
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