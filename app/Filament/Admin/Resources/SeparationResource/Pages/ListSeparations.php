<?php

namespace App\Filament\Admin\Resources\SeparationResource\Pages;

use App\Filament\Admin\Resources\SeparationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeparations extends ListRecords
{
    protected static string $resource = SeparationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
