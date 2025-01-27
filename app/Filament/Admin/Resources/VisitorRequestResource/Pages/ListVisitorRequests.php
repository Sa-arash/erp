<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisitorRequests extends ListRecords
{
    protected static string $resource = VisitorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
