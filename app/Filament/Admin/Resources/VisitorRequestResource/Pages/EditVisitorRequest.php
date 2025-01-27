<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisitorRequest extends EditRecord
{
    protected static string $resource = VisitorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
