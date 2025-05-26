<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
           Section::make([
               TextEntry::make('product.title')->inlineLabel(),
               TextEntry::make('sku')->badge()->inlineLabel(),
               TextEntry::make('serial_number')->badge()->inlineLabel(),
               TextEntry::make('status')->state(fn($record)=>match ($record->status){
                   'inuse' => "In Use", 'inStorageUsable' => "In Storage",  'loanedOut' => "Loaned Out",'outForRepair' => 'Out For Repair','StorageUnUsable' => " Scrap"
               })->badge()->inlineLabel(),
               TextEntry::make('price')->numeric()->inlineLabel(),
               TextEntry::make('warehouse.title')->badge()->inlineLabel(),
               TextEntry::make('structure.title')->badge()->label('Location')->inlineLabel(),
                TextEntry::make('buy_date')->inlineLabel(),
 TextEntry::make('depreciation_years')->inlineLabel(),
 TextEntry::make('depreciation_amount')->inlineLabel(),
               TextEntry::make('employee')->color('aColor')->badge()->state(fn($record)=>$record->employees->last()?->assetEmployee?->employee?->fullName)->inlineLabel(),
           ])->columns(4)
        ]);
    }
}
