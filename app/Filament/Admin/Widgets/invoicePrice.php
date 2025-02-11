<?php

namespace App\Filament\Admin\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;

class InvoicePrice extends ApexChartWidget
{
    // use HasWidgetShield;
    
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'invoicePrice';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Invoice Price';

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

        $debtorData = $months->map(function ($month, $index) {
            return getCompany()->transactions
                ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                ->sum(fn($transaction) => $transaction->debtor);
        })->toArray();

        $creditorData = $months->map(function ($month, $index) {
            return getCompany()->transactions
                ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                ->sum(fn($transaction) => $transaction->creditor);
        })->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'stacked' => true,
            ],
            'series' => [
                [
                    'name' => 'Debtor',
                    'data' => $debtorData,
                ],
                [
                    'name' => 'Creditor',
                    'data' => $creditorData,
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
            'colors' => ['#ef4444', '#22c55e'], // قرمز برای بدهکار، سبز برای بستانکار
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                // 'formatter' => function ($value) {
                //     return number_format($value, 2);
                // },
            ],
        ];
    }
}