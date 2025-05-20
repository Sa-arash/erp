<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyUrgent;
use Filament\Pages\Page;

class MyUrgentLeave extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected ?string $heading='Urgent Leave';

    protected static string $view = 'filament.admin.pages.my-urgent-leave';
    protected static ?string $navigationLabel='Urgent Leave';
    protected function getHeaderWidgets(): array
    {
        return [
            MyUrgent::class,
        ];
    }
}
