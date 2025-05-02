<?php

namespace App\Filament\Admin\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;

class PurchasePrice extends ApexChartWidget
{
    use HasWidgetShield;

    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'Purchase';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Monthly PO Total Cost';

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

        $purchaseData = $months->map(fn($month, $index) => getCompany()->purchaseOrders
            ->filter(fn($request) => Carbon::parse($request->date_of_po)->month == $index + 1)
            ->sum(function($request) {
                $total=0;
                foreach ( $request->items as $item){
                    $freights = intval((float)$item->freights );
                    $q = intval($item->quantity);
                    $tax = intval($item->taxes);
                    $price = $item->unit_price;

                    $total+= ($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100);
                }
                return $total   ;
            } ))->toArray();

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
