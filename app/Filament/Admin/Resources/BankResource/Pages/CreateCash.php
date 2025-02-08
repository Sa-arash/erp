<?php

namespace App\Filament\Admin\Resources\BankResource\Pages;

use App\Filament\Admin\Resources\BankResource;
use App\Filament\Admin\Resources\CashResource;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Currency;
use Filament\Actions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Validation\Rules\Unique;
use function Filament\Support\is_app_url;

class CreateCash extends CreateRecord
{
    protected static string $resource = BankResource::class;
    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make([
                TextInput::make('name')->required()->maxLength(254),
                TextInput::make('code')->default(function () {
                    if (Bank::query()->where('company_id', getCompany()->id)->where('type', 1)->latest()->first()) {
                        return generateNextCode(Bank::query()->where('company_id', getCompany()->id)->latest()->first()->account_code);
                    } else {
                        return "001";
                    }
                })->prefix(fn(Get $get) => Account::query()->firstWhere('id', getCompany()->account_bank)?->code)->required()->maxLength(255)->unique(modifyRuleUsing: function (Unique $rule, $state) {
                    if (getCompany()->account_cash !== null) {
                        $parent = getCompany()->account_cash;
                        $parentAccount = Account::query()->where('id', $parent)->where('company_id', getCompany()->id)->first();

                    } else {
                        $parent = "Cash";
                        $parentAccount = Account::query()->where('stamp', $parent)->where('company_id', getCompany()->id)->first();
                    }
                    return $rule->where('code', $parentAccount . $state)->where('company_id', getCompany()->id);
                })->model(Account::class),
                Select::make('currency_id')->label('Currency')->default(defaultCurrency()?->id)->required()->options(getCompany()->currencies->pluck('name','id'))->searchable()->createOptionForm([
                    Section::make([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('symbol')->required()->maxLength(255),
                        TextInput::make('exchange_rate')->required()->numeric(),
                    ])->columns(3)
                ])->createOptionUsing(function ($data){
                    $data['company_id']=getCompany()->id;
                    Notification::make('success')->title('success')->success()->send();
                    return  Currency::query()->create($data)->getKey();
                }),
            ])->columns(3),
            Textarea::make('description')->columnSpanFull(),
            Hidden::make('type')->default(1),
        ]);
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);
            $data['account_code'] = $data['code'];

            if (getCompany()->account_cash !== null) {
                $parent = getCompany()->account_cash;
                $parentAccount = Account::query()->where('id', $parent)->where('company_id', getCompany()->id)->first();

            } else {
                $parent = "Cash";
                $parentAccount = Account::query()->where('stamp', $parent)->where('company_id', getCompany()->id)->first();
            }
            $check = Account::query()->where('code', $parentAccount->code . $data['account_code'])->where('company_id', getCompany()->id)->first();
            if ($check) {
                Notification::make('error')->title('this Account Code Exist')->warning()->send();
                return;
            }
            $account = Account::query()->create([
                'name' => $data['name'],
                'type' => 'debtor',
                'code' => $parentAccount->code . $data['account_code'],
                'level' => 'detail',
                'parent_id' => $parentAccount->id,
                'built_in' => false,
                'company_id' => getCompany()->id,
                'currency_id'=>$data['currency_id']
            ]);
            $data['account_id'] = $account->id;

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

    protected function getRedirectUrl(): string
    {
        return CashResource::getUrl('index'); // TODO: Change the autogenerated stub
    }

}
