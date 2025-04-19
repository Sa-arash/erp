<?php

namespace App\Filament\Exports;

use App\Models\Task;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
class TaskExporter extends Exporter
{
    protected static ?string $model = Task::class;

//    public function getXlsxHeaderCellStyle(): ?Style
//    {
//        return (new Style())
//            ->setFontBold()
//            ->setFontItalic()
//            ->setFontSize(14)
//            ->setFontName('Consolas')
//            ->setFontColor(Color::rgb(90, 90, 90))
//            ->setBackgroundColor(Color::rgb(30, 30, 30))
//            ->setCellAlignment(CellAlignment::CENTER)
//            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
//    }
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('employee.fullName')->label('Created By'),
            ExportColumn::make('start_date')->label('Start Date'),
            ExportColumn::make('deadline')->label('Due Date'),
            ExportColumn::make('created_at')->label('Assigned Date'),
            ExportColumn::make('start_task')->label('Start Task'),
            ExportColumn::make('end_task')->label('End Task'),
            ExportColumn::make('title')->label('Recently Assigned/ Today'),
            ExportColumn::make('description')->label('Detail'),
            ExportColumn::make('priority_level')->label('Priority Level'),
            ExportColumn::make('status')->state(fn($record)=>$record->status->value),
            ExportColumn::make('employees.fullName'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your task export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
