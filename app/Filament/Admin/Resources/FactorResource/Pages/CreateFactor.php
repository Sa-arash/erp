<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use App\Models\Account;
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
        // dd($this->data);
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


            // dd($this->data);


            DB::beginTransaction(); // شروع تراکنش

            try {

                //     // ذخیره فاکتور (Invoice)

                $this->record->invoice->update([
                    'name' => $this->record->invoice->name . "(Total:" . number_format($total) . ")",
                ]);
                //     // ذخیره تراکنش‌های فاکتور (Transactions)
                foreach ($this->data['invoice']['transactions'] as $transaction) {
                    // dd(str_replace(',', '', $transaction['exchange_rate']));
                    $savedTransaction = $this->record->invoice->transactions()->create([
                        'account_id' => $transaction['account_id'],
                        'description' => $transaction['description'],
                        'company_id' => $transaction['company_id'],
                        'user_id' => auth()->user()->id,
                        'creditor' => str_replace(',', '', $transaction['creditor']),
                        'debtor' => str_replace(',', '', $transaction['debtor']),
                        'Cheque' => $transaction['Cheque'],
                        "currency_id" => $transaction['currency_id'] ?? defaultCurrency()->id,
                        "exchange_rate" => str_replace(',', '', $transaction['exchange_rate']) ?? defaultCurrency()->exchange_rate,
                        "debtor_foreign" => str_replace(',', '', $transaction['debtor_foreign']),
                        "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign']),
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
                // dd(defaultCurrency()->exchange_rate);

                DB::commit(); // تایید تراکنش

                //     return response()->json(['message' => 'Purchase Order Created Successfully', 'data' => $purchaseOrder], 201);

            } catch (\Exception $e) {
                dd($e);
                DB::rollBack(); // لغو تراکنش در صورت خطا
                // return response()->json(['message' => 'Error occurred', 'error' => $e->getMessage()], 500);
            }



















            // dd($total, $this->form->getLivewire()->data,$this->form->getLivewire()->data['type']);
            $party = Parties::find($data['party_id']);
            $account = Account::find($data['account_id']);

            if ($this->form->getLivewire()->data['type'] === 0) {
                //Expense Buy

                // account debtor

                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $this->form->getLivewire()->data['account_id'],
                    'user_id' => auth()->user()->id,
                    "currency_id" => $account->currency_id,
                    "exchange_rate" => $account->currency->exchange_rate,
                    "debtor_foreign" => $total  != 0 ? $total / $account->currency->exchange_rate : 0,
                    // "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign'])!= 0 ? $total/defaultCurrency()->exchange_rate: 0,
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
                    "currency_id" => $party->accountVendor->currency_id,
                    "exchange_rate" => $party->accountVendor->currency->exchange_rate,
                    'creditor' => $total,
                    // "debtor_foreign" => str_replace(',', '', $transaction['debtor_foreign']) != 0 ? $total/defaultCurrency()->exchange_rate: 0,
                    "creditor_foreign" => $total != 0 ? ($total / $party->accountVendor->currency->exchange_rate) : 0,
                    'debtor' => 0,
                    'description' => 'Make '  . $party->name . ' creditor',
                    'company_id' => getCompany()->id,
                    'financial_period_id' => getPeriod()->id,
                    //  'invoice_id' => $invoice->id,


                ]);

                // vendor debtor           
                // dd(str_replace(',', '', $transaction['debtor_foreign']) != 0 ? $total / defaultCurrency()->exchange_rate : 0, str_replace(',', '', $transaction['debtor_foreign']), str_replace(',', '', $transaction['debtor_foreign']) != 0, $total, defaultCurrency()->exchange_rate);

                $savedTransaction = $this->record->invoice->transactions()->create([

                    'account_id' => $party->accountVendor->id,
                    'user_id' => auth()->user()->id,
                    "currency_id" => $party->accountVendor->currency_id,
                    "exchange_rate" => $party->accountVendor->currency->exchange_rate,
                    'creditor' => 0,
                    "debtor_foreign" => $total != 0 ? $total / $party->accountVendor->currency->exchange_rate : 0,
                    // "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign'])!= 0 ? $total/defaultCurrency()->exchange_rate: 0,
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
                    "currency_id" => $party->accountCustomer->currency_id,
                    "exchange_rate" => $party->accountCustomer->currency->exchange_rate,
                    // "debtor_foreign" => str_replace(',', '', $transaction['debtor_foreign']) != 0 ? $total/defaultCurrency()->exchange_rate: 0,
                    "creditor_foreign" => $total != 0 ? $total /  $party->accountCustomer->currency->exchange_rate : 0,
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
                    "currency_id" => $party->accountCustomer->currency_id,
                    "exchange_rate" => $party->accountCustomer->currency->exchange_rate,
                    "debtor_foreign" => $total != 0 ? $total /  $party->accountCustomer->currency->exchange_rate : 0,
                    // "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign'])!= 0 ? $total/defaultCurrency()->exchange_rate: 0,
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
                    "currency_id" => $account->currency_id ,
                    "exchange_rate" => $account->currency->exchange_rate,
                    // "debtor_foreign" => str_replace(',', '', $transaction['debtor_foreign']) != 0 ? $total/defaultCurrency()->exchange_rate: 0,
                    "creditor_foreign" => str_replace(',', '', $transaction['creditor_foreign']) != 0 ? $total / defaultCurrency()->exchange_rate : 0,
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

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }
}
