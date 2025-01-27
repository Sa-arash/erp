<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class TakeOut extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.take-out';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\TakeOut::class
        ];
    }
}
