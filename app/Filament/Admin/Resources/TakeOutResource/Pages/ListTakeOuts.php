<?php

namespace App\Filament\Admin\Resources\TakeOutResource\Pages;

use App\Filament\Admin\Resources\TakeOutResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTakeOuts extends ListRecords
{
    protected static string $resource = TakeOutResource::class;


}
