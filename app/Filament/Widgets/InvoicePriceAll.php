<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class InvoicePriceAll extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'invoicePriceAll';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Companies Invoice Price ';

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
        $companies=Company::query()->with('transactions')->get();
        $debtorData = $months->map(function ($month, $index)use($companies) {
            $total=0;
            foreach ($companies as $company) {
                $total+=$company->transactions
                    ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                    ->sum(fn($transaction) => $transaction->debtor);
            }
            return $total;
        })->toArray();

        $creditorData = $months->map(function ($month, $index)use($companies) {
            $total=0;
            foreach ($companies as $company) {
                $total+=$company->transactions
                    ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                    ->sum(fn($transaction) => $transaction->creditor);
            }
            return $total;
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
