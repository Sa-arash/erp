<?php

namespace App\Filament\Admin\Resources\ProductCategoryResource\Pages;

use App\Filament\Admin\Resources\ProductCategoryResource;
use App\Models\Account;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Extra Setting')->form([
                SelectTree::make('account_id')->default(getCompany()?->account_bank)->disabledOptions(function ($state, SelectTree $component) {
                    return Account::query()->whereIn('level', ['detail','subsidiary'])->where('company_id',getCompany()->id)->pluck('id')->toArray();
                })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Category Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp',"Assets")->where('company_id', getCompany()->id)),
            ])->action(function ($data){
                getCompany()->update([
                    'category_account'=>$data['account_id']
                ]);
            })
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            ProductCategoryResource\Widgets\SubCategory::class
        ];
    }
}
