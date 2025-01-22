<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Filament\Admin\Resources\AccountResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use PHPUnit\TextUI\CliArguments\Builder;

class ListAccounts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Create Account'),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return AccountResource::getWidgets();
    }


    public function getTabs(): array
    {
        $accounts = Account::query()->where('company_id',getCompany()->id)->where('level', 'main')->get();
        $tabs=['All'=>Tab::make()];

        foreach ($accounts as $account) {
            $tabs[$account->name]=Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('parent_id',$account->id)->orWhereHas('account',function ($query)use($account){
                return $query->where('parent_id',$account->id)->orWhereHas('account',function ($query)use($account){
                    return $query->where('parent_id',$account->id)->orWhereHas('account',function ($query)use($account){
                        return $query->where('parent_id',$account->id);
                    });
                });
            }));
        }
        return $tabs;
    }
}
