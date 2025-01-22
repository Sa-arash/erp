<?php

namespace App\Filament\Admin\Resources\WarehouseResource\Pages;

use App\Filament\Admin\Resources\WarehouseResource;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;
    protected function getRedirectUrl(): string
    {
        return WarehouseResource::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
           Actions\Action::make('add')->label('Add Structure')->form([
                TextInput::make('title')->required()->maxLength(255),
                SelectTree::make('parent_id')->label('Parent')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id'),
                Select::make('type')->label('Type')->live()->options(['aisle' => "Aisle", 'room' => 'Room', 'shelf' => "Shelf", 'row' => "Row"])->searchable()->preload()->required()
            ])->action(function ($data,$record) {
                Structure::query()->create([
                    'title'=>$data['title'],
                    'parent_id'=>$data['parent_id'],
                    'warehouse_id'=>$record->id,
                    'type'=>$data['type'],
                    'company_id'=>getCompany()->id,
                ]);
                Notification::make('save')->success()->title('Save ')->send();
            })
        ];
    }
}
