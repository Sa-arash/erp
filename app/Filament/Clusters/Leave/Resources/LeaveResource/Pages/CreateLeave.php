<?php

namespace App\Filament\Clusters\Leave\Resources\LeaveResource\Pages;

use App\Filament\Clusters\Leave\Resources\LeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;
}
