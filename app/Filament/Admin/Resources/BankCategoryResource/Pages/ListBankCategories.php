<?php

namespace App\Filament\Admin\Resources\BankCategoryResource\Pages;

use App\Filament\Admin\Resources\BankCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankCategories extends ListRecords
{
    protected static string $resource = BankCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
