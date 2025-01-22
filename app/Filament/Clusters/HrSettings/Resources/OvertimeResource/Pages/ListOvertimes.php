<?php

namespace App\Filament\Clusters\HrSettings\Resources\OvertimeResource\Pages;

use App\Filament\Clusters\HrSettings\Resources\OvertimeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOvertimes extends ListRecords
{
    protected static string $resource = OvertimeResource::class;

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
            'Rejected'=>  Tab::make()->query(fn($query) => $query->where('status','rejected')),
            'Pending'=>  Tab::make()->query(fn($query) => $query->where('status','pending')),
            'Approved'=>  Tab::make()->query(fn($query) => $query->where('status','accepted')),
        ];
    }
}
