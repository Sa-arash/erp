<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovals extends ListRecords
{
    protected static string $resource = ApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
