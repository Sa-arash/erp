<?php

namespace App\Filament\Admin\Resources\TransActionResource\Pages;

use App\Filament\Admin\Resources\TransActionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransAction extends EditRecord
{
    protected static string $resource = TransActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
