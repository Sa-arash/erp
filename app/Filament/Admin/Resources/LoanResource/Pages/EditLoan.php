<?php

namespace App\Filament\Admin\Resources\LoanResource\Pages;

use App\Filament\Admin\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
