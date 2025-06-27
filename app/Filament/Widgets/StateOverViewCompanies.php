<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StateOverViewCompanies extends BaseWidget
{
    protected static ?int $sort=-2;
    protected function getStats(): array
    {
        return Cache::remember('dashboard_stats_' . auth()->id(), 60, function () {
            $company=getCompany()->id;
            $employeeCount = Employee::query()->where('company_id',$company)->count();
            $assetCount = Asset::query()->where('company_id',$company)->count();
            $assetPriceSum = Asset::query()->where('company_id',$company)->sum('price');
            $projectCount = Project::query()->where('company_id',$company)->count();

            return [
                Stat::make('Employee', number_format($employeeCount)),
                Stat::make('Asset', number_format($assetCount)),
                Stat::make('Asset Price', number_format($assetPriceSum)),
                Stat::make('Active Projects', number_format($projectCount)),
            ];
        });
    }
}
