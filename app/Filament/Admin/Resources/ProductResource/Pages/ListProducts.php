<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Set Category')->form([
                Select::make('accounts')->default(getCompany()->product_accounts)->options(function (){
                    $data=[];
                    $accounts=Account::query()->where('company_id',getCompany()->id)->orderBy('code')->get();
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                })->searchable()->preload()->multiple()

            ])->action(function ($data){

                getCompany()->update(['product_accounts'=>$data['accounts']]);

                Notification::make('accountssuccess')->success()->title('Set accounts successfull')->send();
            }),

            Actions\Action::make('Set Expense')->form([
                Select::make('expense')->default(getCompany()->product_expence_accounts)->options(function (){
                    $data=[];
                    $accounts=Account::query()->where('company_id',getCompany()->id)->where('group','Expense')->orderBy('code')->get();
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                })->searchable()->preload()->multiple()

            ])->action(function ($data){

                getCompany()->update(['product_expence_accounts'=>$data['expense']]);

                Notification::make('success expense')->success()->title('Set Expense Accounts Successfully')->send();
            })
        ];
    }
}
