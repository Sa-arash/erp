<?php

namespace App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource\Pages;

use App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountType extends EditRecord
{
    protected static string $resource = AccountTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
