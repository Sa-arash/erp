<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\accounting;
use App\Filament\Admin\Widgets\assetInStorage;
use App\Filament\Admin\Widgets\invoicePrice;
use App\Filament\Admin\Widgets\profitAndLost;
use Filament\Pages\Page;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.dashboard';
    protected function getHeaderWidgets(): array
    {
        return [
            profitAndLost::class,
            accounting::class,
            invoicePrice::class,
            assetInStorage::class,
        ];
    }
}
