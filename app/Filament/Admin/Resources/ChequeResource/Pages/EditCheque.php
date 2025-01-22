<?php

namespace App\Filament\Admin\Resources\ChequeResource\Pages;

use App\Filament\Admin\Resources\ChequeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheque extends EditRecord
{
    protected static string $resource = ChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return ChequeResource::getUrl('index');
    }
}
