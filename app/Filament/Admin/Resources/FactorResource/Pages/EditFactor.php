<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactor extends EditRecord
{
    protected static string $resource = FactorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
