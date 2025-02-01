<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    public function mount(): void
    {
        if (getPeriod()) {
        Notification::make('financialErors')->seconds(5)->danger()->title('There is no financial period.')->send()->sendToDatabase(auth()->user());
        }
        $this->authorizeAccess();

        $this->loadDefaultActiveTab();
    }
    protected function getHeaderActions(): array
    {
        if (!getPeriod()) {

            return [
                Actions\CreateAction::make()
                    ->url(getPeriod() ? PurchaseOrderResource::getUrl('create') : FinancialPeriodResource::getUrl('index'))->label('New Purchase Order '),
            ];
        }else{
            return [

                Actions\Action::make('financialEror')->label('There is no financial period. Click to create')->url(fn()=>FinancialPeriodResource::getUrl('index'))->color('danger')
            ];
        }
    }
}
