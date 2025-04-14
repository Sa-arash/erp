<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Models\Account;
use App\Models\Department;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Product'),
            Actions\Action::make('Set  Expense')->label('Set Categories For Consumable Products')->form([
                Select::make('expense')->default(getCompany()->product_expence_accounts)->options(function () {
                    $data = [];
                    $accounts = Account::query()->where('company_id', getCompany()->id)->where('group', 'Expense')->orderBy('code')->get();
                    foreach ($accounts as $account) {
                        $data[$account->id] = $account->name . " (" . $account->code . ")";
                    }
                    return $data;
                })->searchable()->preload()->multiple()

            ])->action(function ($data) {

                getCompany()->update(['product_expence_accounts' => $data['expense']]);

                Notification::make('success expense')->success()->title('Set Expense Accounts Successfully')->send();
            }),

                Actions\Action::make('Set Category ')->label('Set Categories For Unconformable Products')->form([
                Select::make('accounts')->default(getCompany()->product_accounts)->options(
                    function () {
                        $data = [];
                        $accounts = Account::query()->where('company_id', getCompany()->id)->orderBy('code')->get();
                        foreach ($accounts as $account) {
                            $data[$account->id] = $account->name . " (" . $account->code . ")";
                        }
                        return $data;
                    }
                )->searchable()->preload()->multiple()

            ])->action(function ($data) {

                getCompany()->update(['product_accounts' => $data['accounts']]);
                Notification::make('accounts success')->success()->title('Set accounts successfully')->send();
            })
        ];
    }
    public function getTabs(): array
    {
        $departments = Department::query()->whereHas('products',function ($query){
        return $query;
        })->get()->pluck('abbreviation','id');
        $tabs=['All'=>Tab::make()];

        foreach ($departments as $key=> $department) {
            $tabs[$department]=Tab::make()->query(fn($query)=>$query->where('department_id',$key));
        }
        return $tabs;
    }

}
