<?php

namespace App\Filament\Admin\Resources\UrgentLeaveResource\Pages;

use App\Filament\Admin\Resources\UrgentLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUrgentLeave extends EditRecord
{
    protected static string $resource = UrgentLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
