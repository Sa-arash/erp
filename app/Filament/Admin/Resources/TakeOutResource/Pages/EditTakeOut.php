<?php

namespace App\Filament\Admin\Resources\TakeOutResource\Pages;

use App\Filament\Admin\Resources\TakeOutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTakeOut extends EditRecord
{
    protected static string $resource = TakeOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
