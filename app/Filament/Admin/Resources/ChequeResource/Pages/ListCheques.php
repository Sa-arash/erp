<?php

namespace App\Filament\Admin\Resources\ChequeResource\Pages;

use App\Filament\Admin\Resources\ChequeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListCheques extends ListRecords
{
    protected static string $resource = ChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(fn()=>getPeriod()?->id),
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
