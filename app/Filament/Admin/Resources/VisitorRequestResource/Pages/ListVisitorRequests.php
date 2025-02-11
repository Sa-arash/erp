<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListVisitorRequests extends ListRecords
{
    protected static string $resource = VisitorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('setSecurity')->label('Set Security')->form([
                Select::make('employee_id')->default(getCompany()->security_id)->required()->label('Security')->options(Employee::query()->where('company_id',getCompany()->id)->get()->pluck('info','id'))->searchable()->preload()
            ])->action(function ($data){
                getCompany()->update(['security_id'=>$data['employee_id']]);
                Notification::make('success')->success()->title('Submit Successfully')->send();
            })->requiresConfirmation()
        ];
    }
}
