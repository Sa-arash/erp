<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use App\Models\Approval;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

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
        $data = ['All' => Tab::make()->query(fn($query) => $query)];
        $approvals = Approval::query()->where('employee_id',getEmployee()->id)->where('company_id', getCompany()->id)->distinct()->get()->unique('approvable_type');
        foreach ($approvals as  $item) {


            $data[Str::headline(substr($item->approvable_type, 11))] = Tab::make()->query(fn($query) => $query->where('approvable_type', $item->approvable_type)) ;
        }
        return $data;

    }
}
