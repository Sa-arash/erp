<?php

namespace App\Filament\Admin\Resources\ProductServiceResource\Pages;

use App\Filament\Admin\Resources\ProductServiceResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProductServices extends ListRecords
{
    protected static string $resource = ProductServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Set Expense')->label('Set Categories For Service')->form([
                Select::make('expense')->default(getCompany()->product_service_accounts)->options(function (){
                    $data=[];
                    $accounts=Account::query()->where('company_id',getCompany()->id)->where('group','Expense')->orderBy('code')->get();
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                })->searchable()->preload()->multiple()

            ])->action(function ($data){
                getCompany()->update(['product_service_accounts'=>$data['expense']]);
                Notification::make('success expense')->success()->title('Set Expense Accounts Successfully')->send();
            })
        ];
    }
}
