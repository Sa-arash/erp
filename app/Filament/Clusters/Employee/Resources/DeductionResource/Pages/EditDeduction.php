<?php

namespace App\Filament\Clusters\Employee\Resources\DeductionResource\Pages;

use App\Filament\Clusters\Employee\Resources\DeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeduction extends EditRecord
{
    protected static string $resource = DeductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
