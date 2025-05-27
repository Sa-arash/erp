<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetResource;
use App\Models\Department;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('AssetTypes')->label('Set Type For Asset')->form([
                TextInput::make('asset_types')->default(getCompany()->asset_types)->options(getCompany()->asset_types)->searchable()->preload()->multiple()

            ])->action(function ($data) {

                getCompany()->update(['asset_types' => $data['asset_types']]);

                Notification::make('success')->success()->title('Set Asset Type Successfully')->send();
            }),
        ];
    }

    public function getTabs(): array
    {
        $departments = Department::query()->whereHas('products',function ($query){
            return $query;
        })->get()->pluck('abbreviation','id');
        $tabs=['All'=>Tab::make()];

        foreach ($departments as $key=> $department) {
            $tabs[$department]=Tab::make()->query(fn($query)=>$query->whereHas('product',function ($query)use($key){
              return  $query->where('department_id',$key);
            }));
        }
        return $tabs;
    }

}
