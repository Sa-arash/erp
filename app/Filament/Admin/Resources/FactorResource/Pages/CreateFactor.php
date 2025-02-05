<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use App\Models\Parties;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateFactor extends CreateRecord
{
    protected static string $resource = FactorResource::class;
    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');



            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

            $total = 0;
            foreach ($this->form->getLivewire()->data['items'] as $item) {
                $total += str_replace(',', '', $item['total']);
            }


            dd($this->record);


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
                        'description' => $transaction['description'] ,
                        'company_id' => $transaction['company_id'],
                        'user_id' => auth()->user()->id,
                        'creditor' => str_replace(',', '', $transaction['creditor']),
                        'debtor' => str_replace(',', '', $transaction['debtor']),
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



















            // dd($total, $this->form->getLivewire()->data,$this->form->getLivewire()->data['type']);
            $party = Parties::find($data['party_id']);

            if ($this->form->getLivewire()->data['type'] === 0) {
                //Expense Buy

                // account debtor

                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $this->form->getLivewire()->data['account_id'],
                    'user_id' => auth()->user()->id,
                    'creditor' => 0,
                    'debtor' => $total,
                    'description' => 'Increas Expence ',
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);



                // vendor creditro
                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $party->accountVendor->id,
                    'user_id' => auth()->user()->id,
                    'creditor' => $total,
                    'debtor' => 0,
                    'description' => 'Make '  . $party->name . ' creditor',
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);

                // vendor debtor
                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $party->accountVendor->id,
                    'user_id' => auth()->user()->id,
                    'creditor' => 0,
                    'debtor' => $total,
                    'description' => 'Give mony to  '  . $party->name,
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);

                // bank done befor






                // dd('ex');





































            } else {
                //Income Sell

                 // customer Creditro 
                 $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $party->accountCustomer->id,
                    'user_id' => auth()->user()->id,
                    'creditor' => $total,
                    'debtor' => 0,
                    'description' => 'Make'  . $party->name . ' Creditor',
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);

                // Customer debtor 
                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $party->accountCustomer->id,
                    'user_id' => auth()->user()->id,
                    'creditor' => 0,
                    'debtor' => $total,
                    'description' => 'Give mony from  '  . $party->name,
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);
               

                // Income Creditor
                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $this->form->getLivewire()->data['account_id'],
                    'user_id' => auth()->user()->id,
                    'creditor' => $total,
                    'debtor' => 0,
                    'description' => 'Increas Income ',
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);



                // bank Done Befor


                // dd('inc');
            }
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

        // $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }
}
