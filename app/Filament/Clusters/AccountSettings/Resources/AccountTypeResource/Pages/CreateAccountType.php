<?php

namespace App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource\Pages;

use App\Filament\Clusters\AccountSettings\Resources\AccountTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountType extends CreateRecord
{
    protected static string $resource = AccountTypeResource::class;
}
