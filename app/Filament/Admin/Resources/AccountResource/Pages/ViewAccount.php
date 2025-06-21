<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Filament\Admin\Resources\AccountResource;
use App\Models\Account;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make([
                TextEntry::make('name'),
                TextEntry::make('parent.name'),
                TextEntry::make('code'),
                TextEntry::make('level'),
                TextEntry::make('type')->state(fn($record)=>$record->type=="debtor"? "Debtor":"Creditor")->color('info')->badge(),
            ])->columns(2),
            Section::make([
                TextEntry::make('debtor')->label('Total Debtor')->state(function ($record) {
                    $id = $record->id;
                    $accounts = Account::query()
                        ->where('id', $id)
                        ->orWhere('parent_id', $id)
                        ->orWhereHas('account', function ($query) use ($id) {
                            $query->where('parent_id', $id)
                                ->orWhereHas('account', function ($query) use ($id) {
                                    $query->where('parent_id', $id);
                                });
                        })
                        ->pluck('id');

                    $totalDebtor = Transaction::query()
                        ->whereIn('account_id', $accounts)
                        ->where('financial_period_id', getPeriod()?->id)
                        ->sum('debtor');

                    return $totalDebtor;
                })->numeric(),
                TextEntry::make('creditor')->label('Total Creditor')->state(function ($record) {
                    $id = $record->id;
                    $accounts = Account::query()
                        ->where('id', $id)
                        ->orWhere('parent_id', $id)
                        ->orWhereHas('account', function ($query) use ($id) {
                            $query->where('parent_id', $id)
                                ->orWhereHas('account', function ($query) use ($id) {
                                    $query->where('parent_id', $id);
                                });
                        })
                        ->pluck('id');

                    $totalDebtor = Transaction::query()
                        ->whereIn('account_id', $accounts)
                        ->where('financial_period_id', getPeriod()?->id)
                        ->sum('creditor');

                    return $totalDebtor;
                })->numeric(),
                TextEntry::make('balance')->state(function ($record) {
                    $id = $record->id;
                    $accounts = Account::query()
                        ->where('id', $id)
                        ->orWhere('parent_id', $id)
                        ->orWhereHas('account', function ($query) use ($id) {
                            $query->where('parent_id', $id)
                                ->orWhereHas('account', function ($query) use ($id) {
                                    $query->where('parent_id', $id);
                                });
                        })->get();

                    if ($record->type == 'debtor') {
                        return $accounts->map(fn($item) => $item->transactions->where('financial_period_id', getPeriod()?->id)
                                ->sum('debtor')-
                            $item->transactions->where('financial_period_id', getPeriod()?->id)->sum('creditor'))->sum();
                    } elseif ($record->type == 'creditor') {
                        return $accounts->map(fn($item) => $item->transactions->where('financial_period_id', getPeriod()?->id)
                                ->sum('creditor')-
                            $item->transactions->where('financial_period_id', getPeriod()?->id)->sum('debtor'))->sum();
                    }
                })->numeric(),
            ])->columns(3)
        ]);
    }
}
