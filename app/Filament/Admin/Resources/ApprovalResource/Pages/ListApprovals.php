<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListApprovals extends ListRecords
{
    protected static string $resource = ApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'All'=>  Tab::make()->query(fn($query) => $query),
            'Pending'=>  Tab::make()->query(fn($query) => $query->where('status','Pending')),
            'Approved'=>  Tab::make()->query(fn($query) => $query->where('status','Approve')),
            'Paid'=>  Tab::make()->query(fn($query) => $query->where('status','payed')),
        ];
    }
}
