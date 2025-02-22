<?php

namespace App\Filament\Admin\Resources\ChequeResource\Pages;

use App\Filament\Admin\Resources\ChequeResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListCheques extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(fn()=>getPeriod()?->id),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return ChequeResource::getWidgets();
    }
    protected function getFooterWidgets(): array
    {
        return [
            ChequeResource\Widgets\ChequeReport::class
        ];
    }

    public function getTabs(): array
    {
        return [
            'All'=>  Tab::make()->query(fn($query) => $query),
            'issued'=>  Tab::make()->query(fn($query) => $query->where('status','issued')),
            'paid'=>  Tab::make()->query(fn($query) => $query->where('status','paid')),
            'returned'=>  Tab::make()->query(fn($query) => $query->where('status','returned')),
        ];
    }
}
