<?php

namespace App\Filament\Admin\Resources\BankResource\Pages;

use App\Filament\Admin\Resources\BankResource;
use App\Models\Account;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListBanks extends ListRecords
{
    protected static string $resource = BankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Bank'),
            Actions\Action::make('Extra Setting')->form([
                SelectTree::make('account_bank')->default(getCompany()?->account_bank)->disabledOptions(function ($state, SelectTree $component) {
                    return Account::query()->where('level', 'detail')->where('company_id',getCompany()->id)->pluck('id')->toArray();
                })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Bank Account SubCategory')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp',"Assets")->where('company_id', getCompany()->id)),
            ])->action(function ($data){
                getCompany()->update([
                    'account_bank'=>$data['account_bank'],
                ]);
            })
        ];
    }

}
