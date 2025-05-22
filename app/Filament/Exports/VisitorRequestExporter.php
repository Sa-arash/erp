<?php

namespace App\Filament\Exports;

use App\Models\VisitorRequest;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Log;

class VisitorRequestExporter extends Exporter
{
    protected static ?string $model = VisitorRequest::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('visit_date'),
            ExportColumn::make('arrival_time'),
            ExportColumn::make('departure_time'),
            ExportColumn::make('agency'),
            ExportColumn::make('purpose'),
            ExportColumn::make('visitors_detail')->formatStateUsing(function ($state) {
                if (!is_array($state)) {
                    return '-';
                }

                return collect($state)->map(fn($item, $index) => ($index + 1) . ") " .

                    "Name: {$item['name']}, " .
                    "ID: {$item['id']}, " .
                    "Phone: {$item['phone']}, " .
                    "Organization: {$item['organization']}, " .
                    "Remarks: {$item['remarks']}")->implode("\n");
            }),
            ExportColumn::make('driver_vehicle_detail'),
            ExportColumn::make('approval_date'),
            ExportColumn::make('status'),
            ExportColumn::make('armed'),
            ExportColumn::make('gate_status'),
            ExportColumn::make('InSide_date'),
            ExportColumn::make('OutSide_date'),
            ExportColumn::make('inSide_comment'),
            ExportColumn::make('OutSide_comment'),
            ExportColumn::make('employee.fullName'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your visitor request export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
