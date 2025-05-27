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
            Actions\CreateAction::make()->label('New Asset'),

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
