<?php

namespace App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource\Pages;

use App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountTypes extends ListRecords
{
    protected static string $resource = AccountTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
