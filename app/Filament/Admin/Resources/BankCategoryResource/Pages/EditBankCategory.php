<?php

namespace App\Filament\Admin\Resources\BankCategoryResource\Pages;

use App\Filament\Admin\Resources\BankCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankCategory extends EditRecord
{
    protected static string $resource = BankCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
