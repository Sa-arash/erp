<?php

namespace App\Filament\Admin\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyProject extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.my-project';
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\MyProject::class,
        ];
    }
}
