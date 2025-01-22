<?php

namespace App\Filament\Admin\Resources\TypeLeaveResource\Pages;

use App\Filament\Admin\Resources\TypeLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeLeave extends EditRecord
{
    protected static string $resource = TypeLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
