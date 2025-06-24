<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPurcheseRequest extends ViewRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Request')->schema([
                Section::make('')->schema([
                    TextEntry::make('request_date')->label('Request Date')->dateTime(),
                    TextEntry::make('purchase_number')->label('Purchase Number')->prefix('ATGT/UNC')->badge(),
                    TextEntry::make('employee.department.title')->label('Department'),
                    TextEntry::make('employee.fullName'),
                    TextEntry::make('employee.position.title')->label('Position'),
                    TextEntry::make('structure')->state(fn($record) => $record->employee->structure?->warehouse?->title . getParents($record->employee->structure))->label('Location'),
                    TextEntry::make('total')->state(function($record) {
                            $total = 0;
                            foreach ($record->items as $item) {
                                $total += $item->quantity * $item->estimated_unit_cost;
                            }
                            return number_format($total,2) ." " .$record->currency?->symbol;
                    } )->label('Total Amount')->size(TextEntry\TextEntrySize::Large),

                    RepeatableEntry::make('approvals')->schema([
                        ImageEntry::make('employee.image')->circular()->label('')->state(fn($record) => $record->employee->media->where('collection_name', 'images')->first()?->original_url),
                        TextEntry::make('employee.fullName')->label(fn($record) => $record->employee?->position?->title),
                        TextEntry::make('read_at')->label('Checked at Date')->dateTime(),
                        TextEntry::make('status')->state(fn($record)=>match ($record->status->value){
                            'Approve'=>"Approved",
                            'NotApprove'=>"Not Approved",
                            'Pending'=>"Pending",
                        })->badge()->color(fn($state)=>match ($state){
                            'Approved'=>"success",
                            'Not Approved'=>"danger",
                            'Pending'=>"primary",
                        }),
                        TextEntry::make('comment')->tooltip(fn($record) => $record->comment)->limit(50),
                        TextEntry::make('approve_date')->dateTime()->label('Approve Date'),
                        ImageEntry::make('employee.signature')->label('')->state(fn($record) => $record->status->value === "Approve" ? $record->employee->media->where('collection_name', 'signature')->first()?->original_url : ''),
                    ])->columns(7)->columnSpanFull()
                ])->columns(3)]),

        ]);

    }
}
