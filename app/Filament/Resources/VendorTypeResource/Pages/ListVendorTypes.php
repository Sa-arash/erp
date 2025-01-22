<?php

namespace App\Filament\Resources\VendorTypeResource\Pages;

use App\Filament\Resources\VendorTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorTypes extends ListRecords
{
    protected static string $resource = VendorTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
