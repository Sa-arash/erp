<?php

namespace App\Filament\Clusters\Employee\Resources\DutyResource\Pages;

use App\Filament\Clusters\Employee\Resources\DutyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDuty extends CreateRecord
{
    protected static string $resource = DutyResource::class;
}
