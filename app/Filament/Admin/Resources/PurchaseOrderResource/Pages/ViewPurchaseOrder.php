<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
      return $infolist ->schema([
           Section::make('Order Info')->schema([
               TextEntry::make('purchase_orders_number')->label('PO No'),
               TextEntry::make('date_of_po')->label('Date of PO')->date(),
               TextEntry::make('purchaseRequest.purchase_number')->label('PR No'),
               TextEntry::make('location_of_delivery')->label('Location of Delivery'),
               TextEntry::make('date_of_delivery')->label('Date of Delivery')->date(),
               TextEntry::make('processed_by.fullName')->label('Processed By'),
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
           ])->columns(3),

       ]);
    }
}
