<?php

namespace App\Filament\Admin\Resources\FinancialPeriodResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Models\FinancialPeriod;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialPeriod extends CreateRecord
{
    protected static string $resource = FinancialPeriodResource::class;

}
