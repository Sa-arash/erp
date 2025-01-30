<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Account;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Throwable;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $total = 0;
            foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {
                $total += str_replace(',', '', $item['total']);
            }

            dd($data, $total, $this->form->getLivewire()->data['RequestedItems'][0]);
            
            $data['status'] = 'approved';
            $invoice = Invoice::query()->create([
                'name' => 'Purchase Order',
                'number' => getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1,
                'date' => $data['date_of_po'],
                'company_id' => getCompany()->id,
            ]);

            // DEBTOR
            $invoice->transactions()->create([
                'account_id' => $data['account_id'],
                'creditor' => $total,
                'debtor' => 0,
                'description' => 'Purchase Order po:' . $data['purchase_orders_number'] . "pr:" . $data['purchase_request_id'],
                'invoice_id' => $invoice->id,
                'financial_period_id' => getPeriod()->id,
                'company_id' => getCompany()->id,
                'user_id' => auth()->user(),
            ]);
            // CREDITOR
            $vendorAccount = Account::find($data['vendor_id']);
            $invoice->transactions()->create([
                'account_id' => $vendorAccount->id,
                'creditor' => 0,
                'debtor' => $total,
                'description' => ' ',

                'invoice_id' => $invoice->id,
                'financial_period_id' => getPeriod()->id,
                'company_id' => getCompany()->id,
                'user_id' => auth()->user(),
            ]);

            ##each item
            $invoice->transactions()->create([
                'account_id' => $vendorAccount->id,
                'creditor' =>  $total,
                'debtor' => 0,
                'description' => ' ',

                'invoice_id' => $invoice->id,
                'financial_period_id' => getPeriod()->id,
                'company_id' => getCompany()->id,
                'user_id' => auth()->user(),
            ]);

            foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {

                $invoice->transactions()->create([
                    'account_id' => $item['product_id'],
                    'creditor' => 0,
                    'debtor' => str_replace(',', '', $item['total']),
                    'description' => 'item buy from ' . $item['purchase_request_id'] ?? '',

                    'invoice_id' => $invoice->id,
                    'financial_period_id' => getPeriod()->id,
                    'company_id' => getCompany()->id,
                    'user_id' => auth()->user(),
                ]);
            }

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

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }
}
