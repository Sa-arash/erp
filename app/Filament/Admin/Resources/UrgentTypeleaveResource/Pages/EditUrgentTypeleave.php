<?php

namespace App\Filament\Admin\Resources\UrgentTypeleaveResource\Pages;

use App\Filament\Admin\Resources\UrgentTypeleaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUrgentTypeleave extends EditRecord
{
    protected static string $resource = UrgentTypeleaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
