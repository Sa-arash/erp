<?php

namespace App\Filament\Admin\Resources\PayPayrollResource\Pages;

use App\Filament\Admin\Resources\PayPayrollResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListPayPayrolls extends ListRecords
{
    protected static string $resource = PayPayrollResource::class;

    public function getTabs(): array
    {
        return [
            'Approved'=>  Tab::make()->query(fn($query) => $query->where('status','accepted')),
            'Paid'=>  Tab::make()->query(fn($query) => $query->where('status','payed')),
        ];
    }
}
