<?php

namespace App\Filament\Admin\Resources\TypeLeaveResource\Pages;

use App\Filament\Admin\Resources\TypeLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeLeaves extends ListRecords
{
    protected static string $resource = TypeLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Leave Type'),
        ];
    }
}
