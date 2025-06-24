<?php

namespace App\Filament\Admin\Resources\UrgentLeaveSecurityResource\Pages;

use App\Filament\Admin\Resources\UrgentLeaveSecurityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUrgentLeaveSecurity extends EditRecord
{
    protected static string $resource = UrgentLeaveSecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
