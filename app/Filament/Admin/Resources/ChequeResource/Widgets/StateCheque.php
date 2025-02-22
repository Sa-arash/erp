<?php

namespace App\Filament\Admin\Resources\ChequeResource\Widgets;

use App\Filament\Admin\Resources\ChequeResource\Pages\ListCheques;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StateCheque extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListCheques::class;
    }
    protected int | string | array $columnSpan=4;
    protected function getStats(): array
    {
        return [
            Stat::make('Count', number_format($this->getPageTableQuery()->count())),
            Stat::make('Receivable', number_format($this->getPageTableQuery()->where('type',0)->sum('amount'))),
            Stat::make('Payable', number_format($this->getPageTableQuery()->where('type',1)->sum('amount'))),
        ];
    }
}
