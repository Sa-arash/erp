<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Log;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('fullName'),
            ExportColumn::make('NIC')->label('NIC'),
            ExportColumn::make('email'),
//            ExportColumn::make('emergency_contact'),
            ExportColumn::make('ID_number')->label('ID Number'),
            ExportColumn::make('type_of_ID')->label('Type Of ID'),
            ExportColumn::make('card_status'),
            ExportColumn::make('immunization'),
            ExportColumn::make('covid_vaccine_certificate'),
            ExportColumn::make('phone_number'),
            ExportColumn::make('birthday'),
            ExportColumn::make('joining_date'),
            ExportColumn::make('leave_date'),
            ExportColumn::make('country'),
            ExportColumn::make('state'),
            ExportColumn::make('city'),
            ExportColumn::make('address'),
            ExportColumn::make('address2'),
            ExportColumn::make('post_code'),
            ExportColumn::make('duty.title'),
            ExportColumn::make('cart'),
            ExportColumn::make('bank'),
            ExportColumn::make('tin'),
            ExportColumn::make('branch'),
            ExportColumn::make('base_salary'),
            ExportColumn::make('daily_salary'),
            ExportColumn::make('benefit_salary'),
            ExportColumn::make('department.title'),
            ExportColumn::make('position.title'),
            ExportColumn::make('contract.title'),
            ExportColumn::make('gender'),
            ExportColumn::make('marriage'),
            ExportColumn::make('count_of_child'),
            ExportColumn::make('emergency_phone_number'),
            ExportColumn::make('warehouse.title')->label('Location/Address'),
            ExportColumn::make('structure.title')->label('Location/Address'),
            ExportColumn::make('blood_group'),
//            ExportColumn::make('company.title'),
            ExportColumn::make('created_at'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';

        }

        return $body;
    }
}
