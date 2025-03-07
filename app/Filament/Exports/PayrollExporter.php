<?php

namespace App\Filament\Exports;

use App\Models\Payroll;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Log;

class PayrollExporter extends Exporter
{
    protected static ?string $model = Payroll::class;

    public static function getColumns(): array
    {
        return [

            ExportColumn::make('employee.fullName'),
            ExportColumn::make('amount_pay'),
            ExportColumn::make('total_deduction'),
            ExportColumn::make('total_allowance'),
            ExportColumn::make('start_date'),
            ExportColumn::make('end_date'),
            ExportColumn::make('payment_date'),
//            ExportColumn::make('status'),
            ExportColumn::make('reference'),
            ExportColumn::make('account.name'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payroll export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
