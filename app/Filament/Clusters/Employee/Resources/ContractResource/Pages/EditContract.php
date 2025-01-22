<?php

namespace App\Filament\Clusters\Employee\Resources\ContractResource\Pages;

use App\Filament\Clusters\Employee\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
