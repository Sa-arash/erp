<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Filament\Admin\Resources\FactorResource;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Parties;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateFactor extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = FactorResource::class;
    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Section::make([
                    Wizard::make(FactorResource::getForm())
                        ->startOnStep($this->getStartStep())
                        ->submitAction($this->getSubmitFormAction())
                        ->skippable($this->hasSkippableSteps())
                        ->contained(false)->columnSpanFull()->afterStateUpdated(function ($state) {

                            $this->afterStepChange($state); // صدا زدن متد Livewire
                        }),
                ])
            ])
            ->columns(null);
    }

    public function afterStepChange($state)
    {

        if (!$state['setPrice']   ) {
            unset($state['invoice']['transactions']);
            $produtTotal = array_map(function ($item) {
                try {
                    return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) - (($item['quantity'] * str_replace(',', '', $item['unit_price'])) * $item['discount']) / 100);
                } catch (\Throwable $th) {
                    return null;
                }
            }, $state['items']);
            if (collect($produtTotal)->sum() >0){
                $state['setPrice']=1;
            }

            $currency = Currency::query()->firstWhere('id',$state['currency_id']);
            if ($state['type'] == '1') {
                $state['invoice']['transactions'] = [
                    [
                        'account_id' => null,
                        'description' => null,
                        'creditor' => 0,
                            'company_id' => getCompany()->id,
                        'debtor' => number_format(round(collect($produtTotal)->sum()*$currency?->exchange_rate,2),2),
                        'exchange_rate' => $currency?->exchange_rate,
                        'debtor_foreign'=>0,
                        'creditor_foreign'=>0

                    ]];
            } else {
                $state['invoice']['transactions'] = [
                    [
                        'account_id' => null,
                        'description' => null,
                        'creditor' => number_format(round(collect($produtTotal)->sum()*$currency?->exchange_rate,2),2),
                        'company_id' => getCompany()->id,
                        'debtor' => 0,
                        'exchange_rate' => $currency?->exchange_rate,
                        'debtor_foreign'=>0,
                        'creditor_foreign'=>0
                    ]];
            }

            $this->form->fill($state);
        }




    }

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
            $currency = Currency::query()->firstWhere('id',$data['currency_id']);

            $total = 0;
            foreach ($this->form->getLivewire()->data['items'] as $item) {
                $total += str_replace(',', '', $item['total']);
            }
            $totalPure=$total;
            $total=$total*$currency?->exchange_rate;

            // dd($this->data);


            DB::beginTransaction(); // شروع تراکنش

            try {

                //     // ذخیره فاکتور (Invoice)

                $this->record->invoice->update([
                    'name' => $this->record->invoice->name . "(Total:" . number_format($totalPure) . ' '.$currency?->name.' '. ")",
                ]);


                //     // ذخیره تراکنش‌های فاکتور (Transactions)
                foreach ($this->data['invoice']['transactions'] as $transaction) {
                   $debtor=str_replace(',', '', $transaction['debtor_foreign'])===""?0:str_replace(',', '', $transaction['debtor_foreign']);
                   $creditor=str_replace(',', '', $transaction['creditor_foreign'])===""?0:str_replace(',', '', $transaction['creditor_foreign']);
                   $ex=str_replace(',', '', $transaction['exchange_rate'])===""?1:str_replace(',', '', $transaction['exchange_rate']);
//                   dd(str_replace(',', '', $transaction['exchange_rate'])!=="" ?str_replace(',', '', $transaction['exchange_rate']): defaultCurrency()->exchange_rate);
                    $savedTransaction = $this->record->invoice->transactions()->create([
                        'account_id' => $transaction['account_id'],
                        'description' => $transaction['description'],
                        'company_id' => $transaction['company_id'],
                        'user_id' => auth()->user()->id,
                        'creditor' => str_replace(',', '', $transaction['creditor']),
                        'debtor' => str_replace(',', '', $transaction['debtor']),
                        'Cheque' => $transaction['Cheque'],
                        "currency_id" => $transaction['currency_id'] ?? defaultCurrency()->id,
                        "exchange_rate" => $ex,
                        "debtor_foreign" => $debtor,
                        "creditor_foreign" => $creditor,
                        'financial_period_id' => getPeriod()->id,
                    ]);



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
            if ($this->form->getLivewire()->data['type'] == 0) {
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
                    'description' => 'Increase Expense',
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
                    'description' => 'Make '  . $party->name . ' Creditor',
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
                    'description' => 'Give Money To  '  . $party->name,
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
                    'description' => 'Give Money From  '  . $party->name,
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
                    'description' => 'Increase Income',
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
//    protected function getRedirectUrl(): string
//    {
//        return FactorResource::getUrl('index'); // TODO: Change the autogenerated stub
//    }
}
