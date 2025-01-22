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
        $finance = FinancialPeriod::query()->where('status', 'During')->where('company_id', getCompany()->id)->first();
        return [
            "All"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('financial_period_id',$finance?->id)),
            "Subsidiary"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('financial_period_id',$finance?->id)->whereHas('account',function ($query){
                return $query->where('level',"subsidiary");
            })),
            "General"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('financial_period_id',$finance?->id)->whereHas('account',function ($query){
                return $query->where('level',"general");
            })),
        ];
    }
}
