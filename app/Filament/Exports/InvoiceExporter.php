<?php

namespace App\Filament\Exports;

use App\Models\Invoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class InvoiceExporter extends Exporter
{
    protected static ?string $model = Invoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('number')->label('Voucher NO'),
            ExportColumn::make('name')->label('Voucher Name'),
            ExportColumn::make('date'),
            ExportColumn::make('reference'),
//            ExportColumn::make('total_debtor')->state(function (Invoice $record): float {
//                $debtor=0;
//                foreach ($record->transactions as $item){
//                    $debtor+=$item->debtor;
//                }
//                return  $debtor;
//            }),
//            ExportColumn::make('total_creditor')->state(function (Invoice $record): float {
//                $creditor=0;
//                foreach ($record->transactions as $item){
//                    $creditor+=$item->creditor;
//                }
//                return  $creditor;
//            }),
            ExportColumn::make('reference'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your invoice export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
