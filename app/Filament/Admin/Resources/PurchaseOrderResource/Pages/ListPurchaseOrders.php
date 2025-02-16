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
        if (getPeriod()==null) {
        Notification::make('financialErors')->seconds(5)->color('danger')->danger()->title('Setup Fiscal Year')->send()->sendToDatabase(auth()->user());
        }
        $this->authorizeAccess();

        $this->loadDefaultActiveTab();
    }
    protected function getHeaderActions(): array
    {
        if (getPeriod()!==null) {

            return [
                Actions\CreateAction::make()
                    ->label('New Purchase Order '),
            ];
        }else{
            return [

                Actions\Action::make('financialEror')->label('Setup Fiscal Year')->url(fn()=>FinancialPeriodResource::getUrl('index'))->color('danger')
            ];
        }
    }
}
