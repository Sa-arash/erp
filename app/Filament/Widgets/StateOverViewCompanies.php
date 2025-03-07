<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StateOverViewCompanies extends BaseWidget
{
    protected static ?int $sort=-2;
    protected function getStats(): array
    {
        return [
            Stat::make('Employee',number_format(Employee::query()->count())),
            Stat::make('Asset',number_format(Asset::query()->count())),
            Stat::make('Asset Price',number_format(Asset::query()->sum('price'))),
            Stat::make('Active Projects',number_format(Project::query()->count())),
        ];
    }
}
