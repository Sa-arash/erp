<?php

namespace App\Filament\Admin\Resources\AccountResource\Widgets;

use App\Filament\Admin\Resources\AccountResource\Pages\ListAccounts;
use App\Models\Account;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListAccounts::class;
    }

    protected function getStats(): array
    {

        if ($this->activeTab === "All"){
            $accounts=Account::query()->with('transactions')->withCount('transactions')->withSum('transactions','creditor')->withSum('transactions','debtor')->get();
            $totalCreditor=0;
            $totalDebtor=0;
            $totalCount=0;
            foreach ($accounts as $account){
                $totalCreditor+=$account->transactions_sum_creditor;
                $totalDebtor+=$account->transactions_sum_debtor;
                $totalCount+=$account->transactions_count;

            }
            return [
//            Stat::make('Orders', $this->getPageTableQuery()->count())
//                ->chart(
//                    $orderData
//                        ->map(fn (TrendValue $value) => $value->aggregate)
//                        ->toArray()
//                ),
                Stat::make('Total Invoices', number_format($totalDebtor))->chart($accounts->pluck('transactions_sum_debtor')->toArray()),
                Stat::make('Total Creditor', number_format($totalCreditor))->chart($accounts->pluck('transactions_sum_creditor')->toArray()),
                Stat::make('Total Debtor', number_format($totalCount)),
//            Stat::make('Open orders', $this->getPageTableQuery()->whereIn('status', ['open', 'processing'])->count()),
//            Stat::make('Average price', number_format($this->getPageTableQuery()->avg('total_price'), 2)),
            ];
        }else{
            return [
//            Stat::make('Orders', $this->getPageTableQuery()->count())
//                ->chart(
//                    $orderData
//                        ->map(fn (TrendValue $value) => $value->aggregate)
//                        ->toArray()
//                ),
//            Stat::make('Open orders', $this->getPageTableQuery()->whereIn('status', ['open', 'processing'])->count()),
//            Stat::make('Average price', number_format($this->getPageTableQuery()->avg('total_price'), 2)),
            ];
        }




    }
}
