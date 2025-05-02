<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyLeave as WidgetsMyLeave;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyLeave extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';
    protected static ?string $navigationLabel = "Leave Request ";
    protected static string $view = 'filament.admin.pages.my-leave';
    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyLeave::class,
        ];
    }
}
