<?php

namespace App\Filament\Admin\Resources\FinancialPeriodResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinancialPeriods extends ListRecords
{
    protected static string $resource = FinancialPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
