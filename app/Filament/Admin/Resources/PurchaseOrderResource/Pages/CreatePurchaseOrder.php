<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;
}
