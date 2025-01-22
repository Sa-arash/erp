<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All'=>  Tab::make()->query(fn($query) => $query),
            'In Use'=>  Tab::make()->query(fn($query) => $query->where('status','inuse')),
            'In Storage Usable'=>  Tab::make()->query(fn($query) => $query->where('status','inStorageUsable')),
            'In Storage UnUsable'=>  Tab::make()->query(fn($query) => $query->where('status','storageUnUsable')),
            'Out For Repair'=>  Tab::make()->query(fn($query) => $query->where('status','outForRepair')),
            'loaned Out'=>  Tab::make()->query(fn($query) => $query->where('status','loanedOut')),
        ];
    }
}
