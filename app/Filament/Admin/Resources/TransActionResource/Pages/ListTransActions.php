<?php

namespace App\Filament\Admin\Resources\TransActionResource\Pages;

use App\Filament\Admin\Resources\TransActionResource;
use App\Models\Account;
use App\Models\FinancialPeriod;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTransActions extends ListRecords
{
    protected static string $resource = TransActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            "All"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query),
            "Subsidiary"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->whereHas('account',function ($query){
                return $query->where('level',"subsidiary");
            })),
            "General"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->whereHas('account',function ($query){
                return $query->where('level',"general");
            })),
        ];
    }
}
