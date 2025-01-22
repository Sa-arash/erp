<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyAsset as WidgetsMyAsset;
use Filament\Pages\Page;

class MyAsset extends Page
{
    protected static ?string $navigationIcon = 'heroicon-c-cube';
    protected static ?string $navigationLabel = "My Assets";

    protected static string $view = 'filament.admin.pages.my-asset';
    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyAsset::class,
        ];
    }
}
