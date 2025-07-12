<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Account;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;

class ProfitAndLost extends ApexChartWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan=['md'=>1,'default'=>4];


    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'profitAndLost';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'P&L';

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

            $incomeData = $months->map(function ($month, $index) {
                return getCompany()->accounts
                    ->where('group', 'Income')
                    ->flatMap(fn($account) => $account->transactions)
                    ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                    ->sum(fn($transaction) => $transaction->creditor - $transaction->debtor);
            })->toArray();

            $expenseData = $months->map(function ($month, $index) {
                return getCompany()->accounts
                    ->where('group', 'Expense')
                    ->flatMap(fn($account) => $account->transactions)
                    ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                    ->sum(fn($transaction) => $transaction->debtor - $transaction->creditor);
            })->toArray();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Profit',
                    'data' => $incomeData,
                ],
                [
                    'name' => 'Loss',
                    'data' => $expenseData,
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
            'colors' => ['#22c55e', '#ef4444'], // سبز برای درآمد، قرمز برای هزینه
            'stroke' => [
                'curve' => 'smooth',
            ],
        ];
    }
}
