<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

                  Section::make([
                      TextInput::make('title_sales_finance')->maxLength(250)->default(getCompany()->title_sales_finance)->columnSpanFull()->required(),
                      TextInput::make('email_finance')->maxLength(250)->default(getCompany()->email_finance)->email(),
                      TextInput::make('phone_finance')->maxLength(250)->default(getCompany()->phone_finance)->tel(),
                      FileUpload::make('stamp')->image()->imageEditor()->default(getCompany()->stamp_finance),
                      FileUpload::make('signature')->image()->imageEditor()->default(getCompany()->signature_finance),
                      Repeater::make('payment_to_finance')->default(getCompany()->payment_to_finance)->schema([
                          TextInput::make('title'),
                          TextInput::make('value'),
                      ])->columns()->columnSpanFull()->addActionLabel('Add')
                  ])->columns()
                ])->action(function ($data){
                    getCompany()->update([
                        'signature_finance'=>$data['signature'],
                        'stamp_finance'=>$data['stamp'],
                        'title_sales_finance'=>$data['title_sales_finance'],
                        'email_finance'=>$data['email_finance'],
                        'phone_finance'=>$data['phone_finance'],
                        'payment_to_finance'=>$data['payment_to_finance']
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
