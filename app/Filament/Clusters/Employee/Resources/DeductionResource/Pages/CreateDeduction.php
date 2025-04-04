<?php

namespace App\Filament\Clusters\Employee\Resources\DeductionResource\Pages;

use App\Filament\Clusters\Employee\Resources\DeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeduction extends CreateRecord
{
    protected static string $resource = DeductionResource::class;
}
