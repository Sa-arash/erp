<?php

namespace App\Filament\Admin\Resources\WarehouseResource\Pages;

use App\Filament\Admin\Resources\WarehouseResource;
use App\Models\Structure;
use App\Models\Warehouse;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Location')->visible(fn()=>\auth()->user()->can('fullManager_warehouse')),
            Actions\Action::make('setWarehouse')->visible(fn()=>\auth()->user()->can('fullManager_warehouse'))->label('Set Default Location and Address')->form([
                Select::make('warehouse_id')->default(getCompany()->warehouse_id)->label('Location')->live()->required()->options(Warehouse::query()->where('company_id',getCompany()->id)->pluck('title','id'))->searchable()->preload(),
                SelectTree::make('structure_id')->default(getCompany()->structure_asset_id)->label('Address')->required()->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query,Get $get){
                    return $query->where('warehouse_id', $get('warehouse_id'));
                }),
            ])->action(function ($data){

                getCompany()->update([
                    'warehouse_id'=>$data['warehouse_id'],
                    'structure_asset_id'=>$data['structure_id']
                ]);
                Notification::make('success')->title('Success ')->color('success')->success()->send();
            })
        ];
    }
}
