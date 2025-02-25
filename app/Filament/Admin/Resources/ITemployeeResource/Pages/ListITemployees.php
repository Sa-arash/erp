<?php

namespace App\Filament\Admin\Resources\ITemployeeResource\Pages;

use App\Filament\Admin\Resources\ITemployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListITemployees extends ListRecords
{
    protected static string $resource = ITemployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
