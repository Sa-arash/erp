<?php

namespace App\Filament\Admin\Pages;


use App\Filament\Admin\Resources\ChequeResource\Widgets\ChequeReport;
use App\Filament\Admin\Resources\ChequeResource\Widgets\StateCheque;
use App\Filament\Admin\Widgets\accounting;
use App\Filament\Admin\Widgets\invoicePrice;
use App\Filament\Admin\Widgets\OverdueChecks;
use App\Filament\Admin\Widgets\profitAndLost;
use App\Filament\Admin\Widgets\PurchasePrice;
use App\Filament\Admin\Widgets\StateOverView;
use App\Filament\Admin\Widgets\StockAlert;
use App\Filament\Admin\Widgets\StockConsumable;
use Filament\Pages\Page;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.dashboard';

    public function mount(){
        if (auth()->user()->need_new_password){
            return  redirect(route('filament.admin.auth.profile'));
        }
    }
    protected function getHeaderWidgets(): array
    {
        return [
            StateOverView::class,
            profitAndLost::class,
            accounting::class,
            invoicePrice::class,
            PurchasePrice::class,
            StockConsumable::class,
            StockAlert::class,
            OverdueChecks::class,
           ChequeReport::class

        ];
    }
}
