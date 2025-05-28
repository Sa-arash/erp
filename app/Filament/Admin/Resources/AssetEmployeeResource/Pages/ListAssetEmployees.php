<?php

namespace App\Filament\Admin\Resources\AssetEmployeeResource\Pages;

use App\Filament\Admin\Resources\AssetEmployeeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListAssetEmployees extends ListRecords
{
    protected static string $resource = AssetEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(' Check OUT'),
        ];
    }
    public function getTabs(): array
    {
        return [
            "All"=>Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query),
            "Check OUT"=>Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('type','Assigned')),
            "Check IN"=>Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('type','Returned')),
        ];
    }
}
