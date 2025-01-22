<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyLeave as WidgetsMyLeave;
use Filament\Pages\Page;

class MyLeave extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';

    protected static string $view = 'filament.admin.pages.my-leave';
    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyLeave::class,
        ];
    }
}
