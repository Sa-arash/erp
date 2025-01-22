<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetResource;
use Filament\Actions;
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
               TextEntry::make('product.title'),
               TextEntry::make('sku')->badge(),
               TextEntry::make('serial_number')->badge(),
               TextEntry::make('status')->badge(),
               TextEntry::make('price')->numeric(),
               TextEntry::make('warehouse.title')->badge()->color('aColor'),
               TextEntry::make('structure.title')->badge()->color('aColor'),
               TextEntry::make('employee')->color('aColor')->badge()->state(fn($record)=>$record->employees->last()?->assetEmployee?->employee?->fullName),
           ])->columns(3)
        ]);
    }
}
