<?php

namespace App\Filament\Admin\Resources\UrgentLeaveResource\Pages;

use App\Filament\Admin\Resources\UrgentLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUrgentLeaves extends ListRecords
{
    protected static string $resource = UrgentLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
