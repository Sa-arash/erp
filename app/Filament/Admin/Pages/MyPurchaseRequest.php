<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyPurchaseRequest as WidgetsMyPurchaseRequest;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyPurchaseRequest extends Page
{

    use HasPageShield;

    protected static string $view = 'filament.admin.pages.my-purchase-request';
    protected static ?string $navigationLabel = "My Purchase Requests";
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $title="My Purchase Requests";


    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyPurchaseRequest::class,
        ];
    }
}
