<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Actions;
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
               TextEntry::make('vendor.info')->label('Vendor'),
               TextEntry::make('currency.name')->label('Currency'),
               TextEntry::make('exchange_rate')->label('Exchange Rate'),
               TextEntry::make('location_of_delivery')->label('Location of Delivery'),
               TextEntry::make('date_of_delivery')->label('Date of Delivery')->date(),
               TextEntry::make('processed_by.fullName')->label('Processed By'),
           ])->columns(3)
       ]);
    }
}
