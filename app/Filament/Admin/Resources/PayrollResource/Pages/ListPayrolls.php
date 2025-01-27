<?php

namespace App\Filament\Admin\Resources\PayrollResource\Pages;

use App\Filament\Admin\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

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
          'Pending'=>  Tab::make()->query(fn($query) => $query->where('status','pending')),
          'Approved'=>  Tab::make()->query(fn($query) => $query->where('status','accepted')),
          'NotApproved'=>  Tab::make()->query(fn($query) => $query->where('status','NotApproved')),
        ];
    }
}
