<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyPayroll as WidgetsMyPayroll;
use App\Models\Task;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyPayRoll extends Page
{

    use HasPageShield;

    protected static ?string $navigationLabel = "My Payroll";
    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static string $view = 'filament.admin.pages.my-pay-roll';

    public static function getNavigationBadge(): ?string
    {
        return now()->format('m');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyPayroll::class,
        ];
    }
}
