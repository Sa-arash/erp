<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StateOverView extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Employee',number_format(Employee::query()->where('company_id',getCompany()->id)->count())),
            Stat::make('Asset',number_format(Asset::query()->where('company_id',getCompany()->id)->count())),
            Stat::make('AssetPrice',number_format(Asset::query()->where('company_id',getCompany()->id)->sum('price'))),
        ];
    }
}
