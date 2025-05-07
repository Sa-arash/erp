<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListVisitorRequests extends ListRecords
{
    protected static string $resource = VisitorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('config')->label('Print Config')->form([
                TextInput::make('title')->default(fn()=>getCompany()->title_security)->required()->maxLength(255),
                FileUpload::make('image')->image()->default(fn()=>getCompany()->logo_security)->imageEditor()->required()
            ])->action(function ($data){
                getCompany()->update(['logo_security'=>$data['image'],'title_security'=>$data['title']]);
                Notification::make('success')->success()->title('Submitted Successfully')->send();
            })->visible(fn()=>auth()->user()->can('logo_and_name_visitor::request'))
        ];
    }
}
