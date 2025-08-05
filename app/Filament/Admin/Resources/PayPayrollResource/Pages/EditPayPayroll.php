<?php

namespace App\Filament\Admin\Resources\PayPayrollResource\Pages;

use App\Filament\Admin\Resources\PayPayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayPayroll extends EditRecord
{
    protected static string $resource = PayPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
