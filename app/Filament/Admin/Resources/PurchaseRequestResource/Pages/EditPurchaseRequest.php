<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRequest extends EditRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
