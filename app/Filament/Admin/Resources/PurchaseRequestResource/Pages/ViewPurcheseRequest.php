<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use Filament\Infolists\Components\ImageEntry;
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
                    TextEntry::make('request_date')->dateTime(),
                    TextEntry::make('purchase_number')->badge(),
                    TextEntry::make('employee.department.title')->label('Department'),
                    TextEntry::make('employee.fullName'),
                    TextEntry::make('position.title'),
                    TextEntry::make('structure')->state(fn($record)=>$record->employee->structure?->warehouse?->title. getParents($record->employee->structure))->label('Location'),
                ])->columns(3)])]);
    }
}
