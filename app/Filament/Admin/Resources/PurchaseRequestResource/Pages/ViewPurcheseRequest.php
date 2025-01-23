<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use Filament\Actions;
use Filament\Infolists\Components\Fieldset;
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
                TextEntry::make('request_date')->date(),
                TextEntry::make('purchase_number')->badge(),
                TextEntry::make('employee.department.title')->label('Department'),
                TextEntry::make('employee.fullName'),
                TextEntry::make('employee.structure.title')->label('Location'),
            ]),
                Fieldset::make('Requested')->relationship('employee')->schema([
                    TextEntry::make('fullName'),
                    TextEntry::make('position.title'),
                    TextEntry::make('structure.title')->label('Duty Station'),
                    ImageEntry::make('signature_pic')
                    ->label('Signature')
                    ->extraImgAttributes(['style' => 'height:60px; width: auto;']),
                    ])->columns(4)
                ]),
        ]);
    }
}
