<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactors extends ListRecords
{
    protected static string $resource = FactorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
