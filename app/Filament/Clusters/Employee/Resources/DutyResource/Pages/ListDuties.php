<?php

namespace App\Filament\Clusters\Employee\Resources\DutyResource\Pages;

use App\Filament\Clusters\Employee\Resources\DutyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDuties extends ListRecords
{
    protected static string $resource = DutyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Duty Type'),
        ];
    }
}
