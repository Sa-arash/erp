<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FinancialPeriodResource\Pages;
use App\Filament\Admin\Resources\FinancialPeriodResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Factor;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Parties;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialPeriodResource extends Resource
{
    protected static ?string $model = FinancialPeriod::class;
    protected static ?string $cluster = FinanceSettings::class;

    protected static ?string $label='Financial Period';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Finance Management';

    public static function getCluster(): ?string
    {
        $period = getPeriod();

        if ($period) {
            return parent::getCluster();
        }
        return '';
    }

    public static function canCreate(): bool
    {
        return getCompany()->financialPeriods->firstWhere('status','During')===null ;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->columnSpanFull()->required()->maxLength(255),
//                Forms\Components\ToggleButtons::make('is_active')
//                ->unique(modifyRuleUsing: function (Unique $rule) {
//                    return $rule->where('is_active', 1)->where('company_id',getCompany()->id);
//                })
//                ->label('Status')->inline()->grouped()->boolean('Active','UnActive')->required(),
                //  Forms\Components\ToggleButtons::make('status')->options(['Before'=>'Before','During'=>'During','End'=>'End'])->inline()->grouped(),
                Forms\Components\DatePicker::make('start_date')->label('Start Date')->default(now()->startOfYear())->required(),
                Forms\Components\DatePicker::make('end_date')->label('End Date')->default(now()->startOfYear()->addYear())->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
//            ->headerActions([
//            Tables\Actions\Action::make('clear')->requiresConfirmation()->action(function (){
//                FinancialPeriod::query()->where('company_id',getCompany()->id)->delete();
//                Invoice::query()->where('company_id',getCompany()->id)->forceDelete();
//                Factor::query()->where('company_id',getCompany()->id)->delete();
//                Bank::query()->where('company_id',getCompany()->id)->delete();
//                Parties::query()->where('company_id',getCompany()->id)->delete();
//                Account::query()->where('company_id',getCompany()->id)->whereNot('built_in',1)->forceDelete();
//                Cheque::query()->where('company_id',getCompany()->id)->delete();
//                $url = "admin/" . getCompany()->id . "/financial-periods";
//                return redirect($url);
//
//            })
//        ])
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label('End Date')->date()->sortable(),
//                Tables\Columns\TextColumn::make('Balance Period')->label('Balance Period')->state(function ($record) {
//                    if ($record->status === "Before") {
//                        return "Initial Journal Entry";
//                    }elseif ($record->status === "End"){
//                        return "End";
//                    }
//                    return "";
//                })->color('aColor')->url(fn($record) => FinancialPeriodResource::getUrl('balance_period', ['record' => $record->id]))->sortable(),
                Tables\Columns\TextColumn::make('status')->alignCenter()->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('balance_period')->label('Opening Balances')->visible(fn($record)=>$record->status->name==="Before")->url(fn($record) => FinancialPeriodResource::getUrl('balance_period', ['record' => $record->id])),
                Tables\Actions\EditAction::make()->visible(fn($record)=>$record->status->name !== 'End'),
                Tables\Actions\Action::make('Close')->requiresConfirmation()
                    ->icon('heroicon-o-check') // اضافه کردن آیکون
                    ->color('danger')->form([
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('name')->label('Fiscal Year Title')->columnSpanFull()->required()->maxLength(255),
                            Forms\Components\DatePicker::make('start_date')->label('Start Date')->default(now()->startOfYear())->required(),
                            Forms\Components\DatePicker::make('end_date')->label('End Date')->default(now()->startOfYear()->addYear())->required(),
                        ])->columns()
                    ])
                    ->action(function ($record, $data) {
                        $record->update(['status' => 'End']);
                        $company = getCompany();
                        $financial = FinancialPeriod::query()->create([
                            'name' => $data['name'],
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'company_id' => $company->id,
                            'status' => 'During'
                        ]);
                        $accounts = Account::query()->with('transactions')->where('company_id', $company->id)->get();
                        $accountsArray = [];

                        foreach ($accounts as $account) {
                            if (isset($account->transactions->where('financial_period_id', $record->id)[1])) {
                                $totalCreditor = 0;
                                $totalDebtor = 0;
                                foreach ($account->transactions->where('financial_period_id', $record->id) as $item) {
                                    $totalCreditor += $item->creditor;
                                    $totalDebtor += $item->debtor;
                                }
                                $total = $totalCreditor - $totalDebtor;
                                $status = $totalCreditor > $totalDebtor ? 0 : 1;

                                if ($total != 0) {
                                    $accountsArray[] = ['account_id' => $account->id, 'status' => $status, 'amount' => abs($total)];
                                }
                            }
                        }
                        if (isset($accountsArray[0])){
                            $invoice = Invoice::query()->create([
                                'name' => 'Start FinancialPeriod ',
                                'number' => 1,
                                'date' => now(),
                                'company_id' => $company->id,
                            ]);
                        }

                        foreach ($accountsArray as $item) {
                            Transaction::query()->create([
                                'account_id' => $item['account_id'],
                                'creditor'=>$item['status'] ===1 ? $item['amount']:0,
                                'debtor'=>$item['status'] ===0 ? $item['amount']:0,
                                'description'=>'Start FinancialPeriod ',
                                'company_id' => $company->id,
                                'user_id'=>auth()->id(),
                                'invoice_id'=>$invoice->id,
                                'financial_period_id'=>$financial->id,
                                'creditor_foreign'=>0,
                                'debtor_foreign'=>0,
                                'currency_id'=>defaultCurrency()->id,
                                'exchange_rate'=>1,
                            ]);
                        }


//                        $url = "admin/" . $company->id . "/financial-periods";
//                        return redirect($url);
                        Notification::make('success')->success()->title('Success')->send();

                    })->modalSubmitActionLabel('Close Previous FY and Open New FY')->modalWidth(MaxWidth::FourExtraLarge)->visible(fn($record) => $record->status->name === 'During'),
//                $record->update($data);
//        if ($data['status'] == "Before") {
//            $url = "admin/" . getCompany()->id . "/financial-periods";
//            return redirect($url);
//        } else {
//            $url = "admin/" . getCompany()->id . "/finance-settings/financial-periods";
//            return redirect($url);
//        }
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialPeriods::route('/'),
            'balance_period' => Pages\BalancePeriod::route('balance-period/{record}')
        ];
    }
}
