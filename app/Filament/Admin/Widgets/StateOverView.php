<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Project;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StateOverView extends BaseWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'State Overview';
    protected static ?string $chartId = 'StateOverView';


    protected function getStats(): array
    {
        return [
            Stat::make('Employee',number_format(Employee::query()->where('company_id',getCompany()->id)->count())),
            Stat::make('Asset',number_format(Asset::query()->where('company_id',getCompany()->id)->count())),
            Stat::make('Asset Price',number_format(Asset::query()->where('company_id',getCompany()->id)->sum('price'))),
            Stat::make('Active Projects',number_format(Project::query()->where('company_id',getCompany()->id)->count())),
        ];
    }
}
