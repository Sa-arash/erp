<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListFactors extends ListRecords
{
    protected static string $resource = FactorResource::class;

    public function mount(): void
    {
        if (getPeriod()==null) {
        Notification::make('financialErors')->seconds(5)->color('danger')->danger()->title('There is no financial period.')->send()->sendToDatabase(auth()->user());
        }
        $this->authorizeAccess();

        $this->loadDefaultActiveTab();
    }
    protected function getHeaderActions(): array
    {
        if (getPeriod()!==null) {

            return [
                Actions\CreateAction::make()
                    ->label('New Invoice'),
            ];
        }else{
            return [

                Actions\Action::make('financialEror')->label('There Is No Financial Period. Click To Create')->url(fn()=>FinancialPeriodResource::getUrl('index'))->color('danger')
            ];
        }
    }
}
