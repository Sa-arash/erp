<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyPayroll as WidgetsMyPayroll;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyPayRoll extends Page
{

    use HasPageShield;
    
    protected static ?string $navigationLabel = "My Payrolls";
    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static string $view = 'filament.admin.pages.my-pay-roll';


    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyPayroll::class,
        ];
    }
}
