<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Task extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.task';
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\Task::class
        ];
    }
}
