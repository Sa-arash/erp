<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyUrgent;
use Filament\Pages\Page;

class MyUrgentLeave extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.my-urgent-leave';
    protected function getHeaderWidgets(): array
    {
        return [
            MyUrgent::class,
        ];
    }
}
