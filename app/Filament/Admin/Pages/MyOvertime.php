<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyOvertime as WidgetsMyOvertime;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyOvertime extends Page
{
    use HasPageShield;
    
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationLabel = "My Overtimes";
    protected static string $view = 'filament.admin.pages.my-overtime';
    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyOvertime::class,
        ];
    }
}
