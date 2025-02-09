<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\FinancialPeriodResource;
use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Parties;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Throwable;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\DB;
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


            // dd($data, $this->data);
            // $total = 0;
            // foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {
            //     $total += str_replace(',', '', $item['total']);
            // }

            // dd($data, $total,   $this->form->getLivewire()->data['invoice']);

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

            // dd($this->data['invoice']['transactions']); 
            $this->callHook('beforeCreate');
// dd( $this->form->model($this->getRecord()));
            $this->record = $this->handleRecordCreation($data);
            $this->form->model($this->getRecord())->saveRelationships();
            // foreach ($this->data['invoice']['transactions'] as $tr){
            //     $this->record->invoice->transactions()->create(
            //         [
            //             $tr['creditor'] = str_replace(',','',$tr['creditor'])
            //             ,
            //             ...$tr
            //         ,
            //         'debtor'=>0,
            //         ]
            //     );
            // }



            $total = 0;
            foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {
                $total += str_replace(',', '', $item['total']);
            }


            DB::beginTransaction(); // شروع تراکنش

            try {

                //     // ذخیره فاکتور (Invoice)

                $this->record->invoice->update([
                    'name' => $this->record->invoice->name . "(Total:" . number_format($total) . ")",
                ]);
                //     // ذخیره تراکنش‌های فاکتور (Transactions)
                foreach ($this->data['invoice']['transactions'] as $transaction) {
                    $savedTransaction = $this->record->invoice->transactions()->create([
                        'account_id' => $transaction['account_id'],
                        'description' => $transaction['description'] . " PONO:" . $this->record->purchase_orders_number . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : ""),
                        'company_id' => $transaction['company_id'],
                        'user_id' => auth()->user()->id,
                        'creditor' => str_replace(',', '', $transaction['creditor']),
                        'debtor' => 0,
                        "currency_id" => $transaction['currency_id'] ?? defaultCurrency()->id,
                        "exchange_rate" => str_replace(',', '', $transaction['exchange_rate']) ?? defaultCurrency()->exchange_rate,
                        "debtor_foreign" => 0,
                        "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign']),
                        'Cheque' => $transaction['Cheque'],
                        'financial_period_id' => $transaction['financial_period_id'],
                    ]);
                    // dd($transaction ,!empty($transaction['cheque']) && isset($transaction['cheque']['amount']) );
                    // چک 
                    if ($transaction['Cheque']) {
                        $savedTransaction->cheque()->create([
                            'type' => $transaction['cheque']['type'] ?? null,
                            'bank_name' => $transaction['cheque']['bank_name'] ?? null,
                            'branch_name' => $transaction['cheque']['branch_name'] ?? null,
                            'account_number' => $transaction['cheque']['account_number'] ?? null,
                            'amount' => str_replace(',', '', $transaction['cheque']['amount']),
                            'issue_date' => $transaction['cheque']['issue_date'] ?? null,
                            'due_date' => $transaction['cheque']['due_date'] ?? null,
                            'status' => $transaction['cheque']['status'] ?? null,
                            'payer_name' => $transaction['cheque']['payer_name'] ?? null,
                            'payee_name' => $transaction['cheque']['payee_name'] ?? null,
                            'description' => $transaction['cheque']['description'] ?? null,
                            'company_id' => $transaction['cheque']['company_id'] ?? null,
                            'status' => 'pending',
                            'cheque_number' => $transaction['cheque']['cheque_number'] ?? null,
                            'transaction_id' => $savedTransaction->id, // اتصال چک به تراکنش
                        ]);
                    }
                }

                DB::commit(); // تایید تراکنش

                //     return response()->json(['message' => 'Purchase Order Created Successfully', 'data' => $purchaseOrder], 201);

            } catch (\Exception $e) {
                dd($e);
                DB::rollBack(); // لغو تراکنش در صورت خطا
                // return response()->json(['message' => 'Error occurred', 'error' => $e->getMessage()], 500);
            }




            #fix
            $vendorAccount = Parties::find($data['vendor_id']);


            //Giving money to Vendor
            $savedTransaction = $this->record->invoice->transactions()->create([

                'account_id' => $vendorAccount->accountVendor->id,
                'user_id' => auth()->user()->id,
                'creditor' => 0,
                "currency_id" => $vendorAccount->accountVendor->currency_id,
                "exchange_rate" => $vendorAccount->accountVendor->currency->exchange_rate,
                "debtor_foreign" => $total != 0 ? $total / $vendorAccount->accountVendor->currency->exchange_rate : 0,
                'debtor' => $total,
                'description' => 'Giving money to ' . $vendorAccount->name . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : "" . " PONO:" . $this->record->purchase_orders_number),
                'company_id' => getCompany()->id,
                'financial_period_id' => getPeriod()->id,
                //  'invoice_id' => $invoice->id,


            ]);

            // Giving assets by vendor
            $savedTransaction = $this->record->invoice->transactions()->create([

                'account_id' => $vendorAccount->accountVendor->id,
                'user_id' => auth()->user()->id,

                "currency_id" => $vendorAccount->accountVendor->currency_id,
                "exchange_rate" => $vendorAccount->accountVendor->currency->exchange_rate,
                "creditor_foreign" => $total != 0 ? ($total / $vendorAccount->accountVendor->currency->exchange_rate) : 0,

                'debtor' => 0,
                'creditor' => $total,
                'description' => 'Get assets from ' . $vendorAccount->name . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : "" . " PONO:" . $this->record->purchase_orders_number),
                'company_id' => getCompany()->id,
                'financial_period_id' => getPeriod()->id,
                //  'invoice_id' => $invoice->id,


            ]);

            //Added each product to asset 

            foreach ($this->form->getLivewire()->data['RequestedItems'] as $item) {

                $product = Product::find($item['product_id']);
                $savedTransaction = $this->record->invoice->transactions()->create([



                    'account_id' => $product->sub_account_id,
                    'user_id' => auth()->user()->id,
                    'creditor' => 0,
                    'debtor' => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)),
                    'description' => 'Added ' . $product->title . ' to assets ' . ($this->record->purchase_request_id ? (" PRNO:" . $this->record->purchaseRequest->purchase_number) : "" . " PONO:" . $this->record->purchase_orders_number),
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,

                    "currency_id" => $product->subAccount->currency_id,
                "exchange_rate" => $product->subAccount->currency->exchange_rate,
                "debtor_foreign" => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)) != 0 ? (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100)) / $product->subAccount->currency->exchange_rate : 0,


                ]);

                // $invoice->transactions()->create([
                //     'account_id' => $item['product_id'],
                //     'creditor' => 0,
                //     'debtor' => str_replace(',', '', $item['total']),
                //     'description' => 'item buy from ' . $item['purchase_request_id'] ?? '',

                //     'invoice_id' => $invoice->id,
                //     'financial_period_id' => getPeriod()->id,
                //     'company_id' => getCompany()->id,
                //     'user_id' => auth()->user(),
                // ]);
            }

            if ($this->record->purchase_request_id) {
                $this->record->purchaseRequest()->update([
                    'status' => "Finished"
                ]);
            }

            // $this->record->update([
            //     'invoice_id'=>$this->record->invoice->id,
            // ]);































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
