<?php

namespace App\Filament\Admin\Resources\CashResource\Pages;

use App\Filament\Admin\Resources\CashResource;
use App\Models\Account;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashes extends ListRecords
{
    protected static string $resource = CashResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Extra Setting')->form([
                SelectTree::make('account_cash')->default(getCompany()?->account_cash)->disabledOptions(function ($state, SelectTree $component) {
                    return Account::query()->where('level', 'detail')->where('company_id',getCompany()->id)->pluck('id')->toArray();
                })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Cash Account SubCategory')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp',"Assets")->where('company_id', getCompany()->id)),
            ])->action(function ($data){
                getCompany()->update([
                    'account_cash'=>$data['account_cash'],
                ]);
            })
        ];
    }
}
