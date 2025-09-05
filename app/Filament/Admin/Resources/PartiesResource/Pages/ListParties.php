<?php

namespace App\Filament\Admin\Resources\PartiesResource\Pages;

use App\Filament\Admin\Resources\PartiesResource;
use App\Models\Account;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListParties extends ListRecords
{
    protected static string $resource = PartiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Extra Setting')->form([
                SelectTree::make('vendor')->default(getCompany()?->vendor_account)->disabledOptions(function ($state, SelectTree $component) {
                    return Account::query()->where('level', 'detail')->where('company_id',getCompany()->id)->pluck('id')->toArray();
                    })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Vendor Account SubCategory ')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp',"Liabilities")->where('company_id', getCompany()->id)),
                SelectTree::make('customer')->default(getCompany()?->customer_account)->disabledOptions(function ($state, SelectTree $component) {
                    return Account::query()->where('level', 'detail')->where('company_id',getCompany()->id)->pluck('id')->toArray();
                })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Customer Account SubCategory')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp',"Assets")->where('company_id', getCompany()->id)),
            ])->action(function ($data){
                getCompany()->update([
                    'customer_account'=>$data['customer'],
                    'vendor_account'=>$data['vendor'],
                ]);
            })
        ];
    }
    public function getTabs(): array
    {

        return [
            "Vendor"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query)=>$query->whereIn('type',['vendor','both'])),
            "Customer"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query)=>$query->whereIn('type',['customer','both'])),
            "Employee"=> Tab::make()->query(fn(\Illuminate\Database\Eloquent\Builder $query)=>$query->where('type',"employee")),
        ];
    }
}
