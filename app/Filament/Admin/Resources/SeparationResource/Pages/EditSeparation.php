<?php

namespace App\Filament\Admin\Resources\SeparationResource\Pages;

use App\Filament\Admin\Resources\SeparationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeparation extends EditRecord
{
    protected static string $resource = SeparationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
