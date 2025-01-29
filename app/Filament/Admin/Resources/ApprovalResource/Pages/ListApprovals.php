<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use App\Models\Approval;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListApprovals extends ListRecords
{
    protected static string $resource = ApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Pending' => Tab::make()->query(fn($query) => $query->where('status', "Pending")),
            'Approve' => Tab::make()->query(fn($query) => $query->where('status', "Approve")),
            'NotApprove' => Tab::make()->query(fn($query) => $query->where('status', "NotApprove")),
            'All' => Tab::make()->query(fn($query) => $query),
        ];
    }
}
