<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Account;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Throwable;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

//     protected function onValidationError(ValidationException $exception): void
// {
//     dd($exception->getMessage() , $exception);
   
// }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');
            
            $data = $this->form->getState();
            
            $this->callHook('afterValidate');
            
            $data = $this->mutateFormDataBeforeCreate($data);
            
           
            
            // $total = 0;
            // foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {
            //     $total += str_replace(',', '', $item['total']);
            // }

            // dd($data, $total, $this->form->getLivewire()->data['invoice']);
            
            // $datainvoice =$this->form->getLivewire()->data['invoice'];
            // $data['status'] = 'approved';
            // $invoice = Invoice::query()->create([
            //     'name' => $datainvoice['name'],
            //     'number' => $datainvoice['number'],
            //     'date' => $datainvoice['date'],
            //     'company_id' => getCompany()->id,
            // ]);
            // dd($invoice,$data, $total, $this->form->getLivewire()->data['invoice']);

            // // DEBTOR
            // foreach($datainvoice['transactions'] as $transAction)
            // {
            //     $invoice->transactions()->create([
            //         'account_id' =>  $transAction['account_id'],
            //         'creditor' =>  $transAction['creditor'],
            //         'debtor' =>  0,
            //         'description' =>  $transAction['description'],
            //         'invoice_id' => $invoice->id,
            //         'financial_period_id' =>  $transAction[''],
            //         'company_id' =>  $transAction[''],
            //         'user_id' => auth()->user(),
            //     ]);
            // }
           
            // // CREDITOR
            // $vendorAccount = Account::find($data['vendor_id']);


            // $invoice->transactions()->create([
            //     'account_id' => $vendorAccount->id,
            //     'creditor' => 0,
            //     'debtor' => $total,
            //     'description' => ' ',

            //     'invoice_id' => $invoice->id,
            //     'financial_period_id' => getPeriod()->id,
            //     'company_id' => getCompany()->id,
            //     'user_id' => auth()->user(),
            // ]);

            // ##each item
            // $invoice->transactions()->create([
            //     'account_id' => $vendorAccount->id,
            //     'creditor' =>  $total,
            //     'debtor' => 0,
            //     'description' => ' ',

            //     'invoice_id' => $invoice->id,
            //     'financial_period_id' => getPeriod()->id,
            //     'company_id' => getCompany()->id,
            //     'user_id' => auth()->user(),
            // ]);

            // foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {

            //     $invoice->transactions()->create([
            //         'account_id' => $item['product_id'],
            //         'creditor' => 0,
            //         'debtor' => str_replace(',', '', $item['total']),
            //         'description' => 'item buy from ' . $item['purchase_request_id'] ?? '',

            //         'invoice_id' => $invoice->id,
            //         'financial_period_id' => getPeriod()->id,
            //         'company_id' => getCompany()->id,
            //         'user_id' => auth()->user(),
            //     ]);
            // }

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);
            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        // $redirectUrl = $this->getRedirectUrl();

        // $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }
}
