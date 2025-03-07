<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProfitAndLost extends ApexChartWidget
{
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
    protected static ?string $heading = 'Companies Profit And Lost';

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
        $companies = Company::query()->with('transactions')->get();

        $incomeData = $months->map(function ($month, $index) use ($companies) {
            $total = 0;
            foreach ($companies as $company) {
                $total += $company->accounts
                    ->where('group', 'Income')
                    ->flatMap(fn($account) => $account->transactions)
                    ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                    ->sum(fn($transaction) => $transaction->creditor - $transaction->debtor);
            }
            return $total;

        })->toArray();

        $expenseData = $months->map(function ($month, $index) use ($companies) {
            $total = 0;
            foreach ($companies as $company) {
                $total += $company->accounts
                    ->where('group', 'Expense')
                    ->flatMap(fn($account) => $account->transactions)
                    ->filter(fn($transaction) => Carbon::parse($transaction->created_at)->month == $index + 1)
                    ->sum(fn($transaction) => $transaction->debtor - $transaction->creditor);
            }
            return $total;
        }
        )->toArray();

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
