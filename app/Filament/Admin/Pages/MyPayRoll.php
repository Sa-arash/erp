<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyPayroll as WidgetsMyPayroll;
use Filament\Pages\Page;

class MyPayRoll extends Page
{

    protected static ?string $navigationLabel = "My Payroll";
    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static string $view = 'filament.admin.pages.my-pay-roll';


    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyPayroll::class,
        ];
    }
}
