<?php

namespace App\Filament\Admin\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class Separation extends Page
{
    use HasPageShield;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.sepration';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\Separation::class
        ];
    }
}
