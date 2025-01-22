<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyPurchaseRequest as WidgetsMyPurchaseRequest;
use Filament\Pages\Page;

class MyPurchaseRequest extends Page
{

    protected static string $view = 'filament.admin.pages.my-purchase-request';
    protected static ?string $navigationLabel = "My Purchase Requests";
    protected static ?string $navigationIcon = 'heroicon-s-credit-card';


    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyPurchaseRequest::class,
        ];
    }
}
