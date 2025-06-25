<?php

namespace App\Filament\Admin\Resources\GrnResource\Pages;

use App\Filament\Admin\Resources\GrnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrns extends ListRecords
{
    protected static string $resource = GrnResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            GrnResource\Widgets\PurchaseOrderGRN::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New GRN')->visible(fn()=>auth()->user()->can('Head Logistic_grn')),
        ];
    }
    protected static ?string $title='Good Receipt Note (GRN)';
}
