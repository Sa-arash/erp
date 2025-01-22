<?php

namespace App\Filament\Admin\Resources\ChequeResource\Pages;

use App\Filament\Admin\Resources\ChequeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCheque extends CreateRecord
{
    protected static string $resource = ChequeResource::class;

    protected function getRedirectUrl(): string
    {
        return ChequeResource::getUrl('index');
    }

}
