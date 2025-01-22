<?php

namespace App\Filament\Clusters\HrSettings\Resources\OvertimeResource\Pages;

use App\Filament\Clusters\HrSettings\Resources\OvertimeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOvertime extends EditRecord
{
    protected static string $resource = OvertimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
