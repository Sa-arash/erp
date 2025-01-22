<?php

namespace App\Filament\Clusters\Employee\Resources\DutyResource\Pages;

use App\Filament\Clusters\Employee\Resources\DutyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDuty extends EditRecord
{
    protected static string $resource = DutyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
