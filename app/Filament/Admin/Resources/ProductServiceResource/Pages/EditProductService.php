<?php

namespace App\Filament\Admin\Resources\ProductServiceResource\Pages;

use App\Filament\Admin\Resources\ProductServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductService extends EditRecord
{
    protected static string $resource = ProductServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
