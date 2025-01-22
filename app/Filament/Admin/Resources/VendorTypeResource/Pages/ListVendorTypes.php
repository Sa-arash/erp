<?php

namespace App\Filament\Admin\Resources\VendorTypeResource\Pages;

use App\Filament\Admin\Resources\VendorTypeResource;
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
