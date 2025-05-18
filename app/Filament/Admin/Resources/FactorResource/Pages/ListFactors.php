<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListFactors extends ListRecords
{
    protected static string $resource = FactorResource::class;

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
                Actions\CreateAction::make()->label('New Invoice'),
                Actions\Action::make('config')->form([
                    FileUpload::make('stamp')->default(getCompany()->stamp_finance),
                    FileUpload::make('signature')->default(getCompany()->signature_finance),
                ])->action(function ($data){
                    getCompany()->update([
                        'signature_finance'=>$data['signature'],
                        'stamp_finance'=>$data['stamp']
                    ]);
                    Notification::make('success')->success()->title('Saved')->send();

                })

            ];
        }else{
            return [

                Actions\Action::make('financialEror')->label('Setup Fiscal Year')->url(fn()=>FinancialPeriodResource::getUrl('index'))->color('danger')
            ];
        }
    }
}
