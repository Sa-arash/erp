<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AdvanceReport as WidgetsAdvanceReport;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use App\Models\Account;
use App\Models\FinancialPeriod;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class AdvanceReport extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = "Accounting Report";

    protected static string $view = 'filament.admin.pages.advance-report';

    public static function canAccess(): bool
    {
      return (getPeriod()  && (auth()->user()->can('page_AdvanceReport')));
    }

    protected function getActions(): array
{
    return  [
        Action::make('journal')
            ->label('Journal')->modal()
            ->form([
                DatePicker::make('from_date')
                    ->label('From Date')
                    ->columnSpan(1),
                DatePicker::make('to_date')
                    ->label('To Date')
                    ->columnSpan(1),
                Select::make('financial_period')->searchable()
                    ->label('Financial Period')
                    ->default(getPeriod()?->id)
                    ->options(getCompany()->financialPeriods->pluck('name', 'id'))
                    ->required(),
            ])
            ->action(function (array $data) {
                $transactions = Transaction::where('financial_period_id', $data['financial_period']);

                if (isset($data['from_date']) && isset($data['to_date'])) {
                    $transactions = $transactions->whereHas('invoice', function ($query) use ($data) {
                        $query->whereBetween('date', [$data['from_date'], $data['to_date']]);
                    });
                }

                // dd($transactions->get(),(implode('-',$transactions->pluck('id')->toArray())));

                return redirect()->route('pdf.jornal', [
                    'transactions' => (implode('-', $transactions->pluck('id')->toArray())),


                ]);
            }),

        Action::make('General Ledger')
            ->label('General Ledger')->modal()
            ->form([
                DateRangePicker::make('date'),
                Select::make('financial_period')->searchable()
                    ->label('Financial Period')
                    ->default(getPeriod()?->first()->id)
                    ->options(getCompany()->financialPeriods->pluck('name', 'id'))
                    ->required(),
                Select::make('accounts_id')->multiple()
                    ->label('Account')
                    // ->default(getCompany()->accounts->where('level', "During")?->first())
                    ->options(getCompany()->accounts->where('level', "general")->pluck('name', 'id'))
                    ->required(),

            ])
            ->action(function (array $data) {
                if ($data['date']) {
                    return redirect()->route('pdf.account', [
                        'period' => $data['financial_period'],
                        'reportTitle' => 'General Leadger',
                        'account' => implode('-', $data['accounts_id']),
                        'date' => str_replace('/', '-', $data['date'])
                    ]);
                } else {
                    return redirect()->route('pdf.account', [
                        'period' => $data['financial_period'],
                        'reportTitle' => 'General Leadger',
                        'account' => implode('-', $data['accounts_id']),
                    ]);
                }
            }),
        Action::make('Subsidiary Leadger')
            ->label('Subsidiary Leadger')->modal()
            ->form([
                DateRangePicker::make('date'),
                Select::make('financial_period')->searchable()
                    ->label('Financial Period')
                    ->default(getPeriod()?->first()->id)
                    ->options(getCompany()->financialPeriods->pluck('name', 'id'))
                    ->required(),
                Select::make('accounts_id')->multiple()
                    ->label('Account')
                    // ->default(getCompany()->accounts->where('level', "During")?->first())
                    ->options(getCompany()->accounts->where('level', "subsidiary")->pluck('name', 'id'))
                    ->required(),

            ])
            ->action(function (array $data) {
                if ($data['date']) {
                    return redirect()->route('pdf.account', [
                        'period' => $data['financial_period'],
                        'reportTitle' => 'Subsidiary Leadger',
                        'account' => implode('-', $data['accounts_id']),
                        'date' => str_replace('/', '-', $data['date'])
                    ]);
                } else {
                    return redirect()->route('pdf.account', [
                        'period' => $data['financial_period'],
                        'reportTitle' => 'Subsidiary Leadger',
                        'account' => implode('-', $data['accounts_id']),
                    ]);
                }
            }),
        Action::make('Profit and Loss')
            ->label('Profit and Loss')->modal()
            ->form([
                DateRangePicker::make('date'),
                Select::make('financial_period')->searchable()
                    ->label('Financial Period')
                    ->default(getPeriod()?->first()->id)
                    ->options(getCompany()->financialPeriods->pluck('name', 'id'))
                    ->required(),
                Select::make('accounts_id')->multiple()
                    ->label('Account')
                    ->options(
                        function () {

                            $accountsID =  getCompany()->accounts->whereIn('stamp', ['Income', 'Expenses'])->pluck('id')->toArray();
                            $accounts = Account::query()->whereIn('id', $accountsID)->orWhereIn('parent_id', $accountsID)
                                ->orWhereHas('account', function ($query) use ($accountsID) {
                                    return $query->whereIn('parent_id', $accountsID)->orWhereHas('account', function ($query) use ($accountsID) {
                                        return $query->whereIn('parent_id', $accountsID);
                                    });
                                })
                                ->get();
                            return ($accounts->pluck('name', 'id'));
                        }
                    )->required(),
            ])
            ->action(function (array $data) {
                if ($data['date']) {
                    return redirect()->route('pdf.account', [
                        'period' => $data['financial_period'],
                        'reportTitle' => 'Profit and Loss',
                        'account' => implode('-', $data['accounts_id']),
                        'date' => str_replace('/', '-', $data['date']),
                    ]);
                } else {
                    return redirect()->route('pdf.account', [
                        'period' => $data['financial_period'],
                        'reportTitle' => 'Profit and Loss',
                        'account' => implode('-', $data['accounts_id']),
                    ]);
                }
            }),

        Action::make('Trial Balance')
            ->label('Trial Balance')
            ->modal()
            ->form([
                DatePicker::make('date')
                    ->label('Until Date'),
                Select::make('financial_period')
                    ->searchable()
                    ->label('Financial Period')
                    ->default(getPeriod()?->first()?->id)
                    ->options(getCompany()->financialPeriods->pluck('name', 'id'))
                    ->required(),
            ])
            ->action(function (array $data) {
                if ($data['date']) {
                    return redirect()->route('pdf.trialBalance', [
                        'period' => $data['financial_period'],
                        'date' => $data['date']
                    ]);
                } else {
                    return redirect()->route('pdf.trialBalance', [
                        'period' => $data['financial_period'],
                    ]);
                }
            }),
        Action::make('Balance Sheet')
            ->label('Balance Sheet')->modal()
            ->form([
                DatePicker::make('date')
                    ->label('Until Date'),
                Select::make('financial_period')->searchable()
                    ->label('Financial Period')
                    ->default(getPeriod()?->first()->id)
                    ->options(getCompany()->financialPeriods->pluck('name', 'id'))
                    ->required(),

            ])
            ->action(function (array $data) {
                if ($data['date']) {
                    return redirect()->route('pdf.balance', [
                        'period' => $data['financial_period'],
                        'date' => $data['date'],
                    ]);
                } else {
                    return redirect()->route('pdf.balance', [
                        'period' => $data['financial_period'],

                    ]);
                }
            }),
    ];
}

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         WidgetsAdvanceReport::class
    //     ];
    // }
}
