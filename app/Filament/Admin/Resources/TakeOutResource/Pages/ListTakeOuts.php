<?php

namespace App\Filament\Admin\Resources\TakeOutResource\Pages;

use App\Filament\Admin\Resources\TakeOutResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTakeOuts extends ListRecords
{
    protected static string $resource = TakeOutResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('setSecurity')->label('Set Security')->form([
                Select::make('employee_id')->default(getCompany()->security_id)->required()->label('Security')->options(Employee::query()->where('company_id',getCompany()->id)->get()->pluck('info','id'))->searchable()->preload()
            ])->action(function ($data){
                getCompany()->update(['security_id'=>$data['employee_id']]);
                Notification::make('success')->success()->title('Submit Successfully')->send();
            })->requiresConfirmation()
        ];
    }

}
