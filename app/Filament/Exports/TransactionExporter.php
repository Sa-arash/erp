<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('invoice.name')->label('Voucher Name'),
            ExportColumn::make('account.name'),
            ExportColumn::make('description'),
            ExportColumn::make('creditor')->state(fn($state)=> number_format($state,4)),
            ExportColumn::make('debtor')->state(fn($state)=> number_format($state,4)),
            ExportColumn::make('currency.name'),
            ExportColumn::make('exchange_rate')->state(fn($state)=> number_format($state,2)),
            ExportColumn::make('creditor_foreign'),
            ExportColumn::make('debtor_foreign'),
            ExportColumn::make('reference'),
            ExportColumn::make('financialPeriod.name'),
            ExportColumn::make('user.name'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
