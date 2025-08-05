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
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make('delete')->action(function ($record) {
                $record->forceDelete();
                return $this->redirect(AccountResource::getUrl('index'));
            })->hidden(fn($record) => $record->built_in === 1 or $record->transactions->count() > 0 or $record->childerns->count() or $record->products->count() or $record->productsSub->count()),
        ];
    }
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
                TextEntry::make('debtor')->suffix(" ".defaultCurrency()->name)->label('Total Debtor')->state(function ($record) {
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
                TextEntry::make('creditor')->suffix(" ".defaultCurrency()->name)->label('Total Creditor')->state(function ($record) {
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
                TextEntry::make('balance')->suffix(" ".defaultCurrency()->name)->state(function ($record) {
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
            ])->columns(3),
            Section::make()->visible(fn($record)=> $record->currency_id != defaultCurrency()->id)->schema([
                TextEntry::make('debtor_foreign')->suffix(" ".$this->record->currency->name)->label('Total Debtor')->state(function ($record) {
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
                        ->sum('debtor_foreign');

                    return $totalDebtor;
                })->numeric(),
                TextEntry::make('creditor_foreign')->suffix(" ".$this->record->currency->name)->label('Total Creditor')->state(function ($record) {
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
                        ->sum('creditor_foreign');

                    return $totalDebtor;
                })->numeric(),
                TextEntry::make('balance')->suffix(" ".$this->record->currency->name)->state(function ($record) {
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
                                ->sum('debtor_foreign')-
                            $item->transactions->where('financial_period_id', getPeriod()?->id)->sum('creditor_foreign'))->sum();
                    } elseif ($record->type == 'creditor') {
                        return $accounts->map(fn($item) => $item->transactions->where('financial_period_id', getPeriod()?->id)
                                ->sum('creditor_foreign')-
                            $item->transactions->where('financial_period_id', getPeriod()?->id)->sum('debtor_foreign'))->sum();
                    }
                })->numeric(),
            ])->columns(3)


        ]);
    }
}
