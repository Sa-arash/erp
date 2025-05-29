<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetEmployeeResource;
use App\Filament\Admin\Resources\AssetResource;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\AssetEmployeeItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Check OUT')->label('Check OUT')->color('success')->url(fn($record) => AssetEmployeeResource::getUrl('create', ['asset' => $record->id]))->disabled(fn($record) => $record->assetEmployee?->last()?->type === "Assigned"),
            Action::make('approve')->form([
                Placeholder::make('')->content(function ($record) {
                    return view('partials.asset-employee-info', ['record' => $record->assetEmployee?->last()]);
                }),
                Textarea::make('note')
            ])->iconSize(IconSize::Medium)->color('success')->disabled(fn($record) => $record->assetEmployee?->last()?->type !== "Returned")->icon('heroicon-s-check')->label('Approve Check IN')->requiresConfirmation()->action(function ($record, $data) {
                $record->update([
                    'status' => "Approve",
                    'note' => $data['note']
                ]);
                foreach ($record->assetEmployeeItem as $item) {
                    AssetEmployeeItem::query()->where('asset_id', $item->asset_id)->update(['type' => 1,]);
                    $item->update(['return_approval_date' => now()]);
                    Asset::query()->where('id', $item->asset_id)->update(['status' => 'inStorageUsable', 'warehouse_id' => $item->warehouse_id, 'structure_id' => $item->structure_id,]);
                }
                Notification::make('success')->success()->title('Approved')->send();
            })->modalIcon('heroicon-s-check')->modalWidth(MaxWidth::FiveExtraLarge),
            Action::make('Check IN')->label('Check IN')->color('warning')->url(fn($record) => $record->assetEmployee?->last()?->type === "Returned" ? AssetEmployeeResource::getUrl('edit', ['record' => $record->assetEmployee?->last()->id]) : false),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make([


                Group::make([
                    TextEntry::make('product.sku')->label('SKU')->badge()->inlineLabel(),
                    TextEntry::make('product.title')->inlineLabel(),
                    TextEntry::make('description')->inlineLabel(),
                    TextEntry::make('serial_number')->label("Serial Number")->badge()->inlineLabel(),
                    TextEntry::make('po_number')->label("Po Number")->badge()->inlineLabel(),
                    TextEntry::make('status')->state(fn($record) => match ($record->status) {
                        'inuse' => "In Use",
                        'inStorageUsable' => "In Storage",
                        'loanedOut' => "Loaned Out",
                        'outForRepair' => 'Out For Repair',
                        'StorageUnUsable' => " Scrap"
                    })->badge()->inlineLabel(),
                    TextEntry::make('price')->numeric()->inlineLabel(),
                    TextEntry::make('scrap_value')->label("Scrap Value")->numeric()->inlineLabel(),
                    //description
                    // note
                    // 
                    // 
                    // 
                    // check_out_to
                    // party_id
                    TextEntry::make('warehouse.title')->badge()->inlineLabel(),
                    TextEntry::make('structure.title')->badge()->label('Location')->inlineLabel(),
                    TextEntry::make('check_out_to.fullname')->badge()->label('Check Out To')->inlineLabel(),
                    TextEntry::make('party.name')->badge()->label('Vendor')->inlineLabel(),
                    TextEntry::make('buy_date')->inlineLabel()->label('Buy Date'),
                    TextEntry::make('guarantee_date')->inlineLabel()->label('Due Date'),
                    TextEntry::make('warranty_date')->inlineLabel()->label('Warranty End'),
                    TextEntry::make('type')->badge()->label('Asset Type')->inlineLabel(),
                    TextEntry::make('depreciation_years')->inlineLabel()->label('Depreciation Years'),
                    TextEntry::make('depreciation_amount')->inlineLabel()->label('Depreciation Amount'),
                    TextEntry::make('employee')->color('aColor')->badge()->state(fn($record) => $record->employees->last()?->assetEmployee?->employee?->fullName)->inlineLabel(),
                ]),

                Group::make([
                    ImageEntry::make('media.original_url')->state(function ($record) {
                        return $record->media->where('collection_name', 'images')->first()?->original_url;
                    })->disk('public')
                        ->defaultImageUrl(fn($record) => asset('img/defaultAsset.png'))
                        ->alignLeft()->label('Asset Picture')->width(200)->height(200)->extraAttributes(['style' => 'border-radius:50px!important']),


                        TextEntry::make('note'),
                    RepeatableEntry::make('attributes')
                        ->schema([
                            TextEntry::make('title'),
                            TextEntry::make('value'),
                        ])
                        ->columns(3),
                ]),



            ])->columns(2)
        ]);
    }
}
