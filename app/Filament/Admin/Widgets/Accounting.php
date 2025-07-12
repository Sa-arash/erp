<?php

namespace App\Filament\Admin\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;

class Accounting extends ApexChartWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan=['md'=>1,'default'=>4];

    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'accounting';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Accounting';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $accounts = getCompany()->accounts->groupBy('group');

        $labels = $accounts->keys()->toArray();
        $series = $accounts->map(
            fn($group) =>
            $group->flatMap(fn($account) => $account->transactions)
                ->sum(fn($transaction) => $transaction->account->type == 'creditor'? $transaction->creditor - $transaction->debtor:$transaction->debtor-$transaction->creditor )
        )->values()->toArray();
        // dd($accounts);
        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'colors' => ['#6366f1', '#f59e0b', '#ef4444', '#22c55e', '#8b5cf6'],
        ];
    }
}
