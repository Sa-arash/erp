<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        if (getPeriod()) {

            return [
                Actions\CreateAction::make()
                    ->url(getPeriod() ? PurchaseOrderResource::getUrl('create') : FinancialPeriodResource::getUrl('index'))->label('New Purchase Order '),
            ];
        }else{
            return [];
        }
    }
}
