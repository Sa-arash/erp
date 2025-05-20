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
            Actions\CreateAction::make()->label(' New Visitor Access Request'),
            Actions\Action::make('config')->label('Print Config')->form([
                TextInput::make('title')->default(fn()=>getCompany()->title_security)->required()->maxLength(255),
                TextInput::make('description_security')->label('Description')->default(fn()=>getCompany()->description_security)->required()->maxLength(255),
                TextInput::make('SOP_number')->label('SOP Number')->default(fn()=>getCompany()->SOP_number)->required()->maxLength(100),
                TextInput::make('supersedes_security')->label('Supersedes')->default(fn()=>getCompany()->supersedes_security)->required()->maxLength(100),
                TextInput::make('effective_date_security')->label('Effective Date_security')->default(fn()=>getCompany()->effective_date_security)->required()->maxLength(100),
                FileUpload::make('image')->image()->default(fn()=>getCompany()->logo_security)->imageEditor()->required(),

            ])->action(function ($data){
                getCompany()->update(['supersedes_security'=>$data['supersedes_security'],'logo_security'=>$data['image'],'title_security'=>$data['title'],'SOP_number'=>$data['SOP_number'],'description_security'=>$data['description_security'],'effective_date_security'=>$data['effective_date_security']]);
                Notification::make('success')->success()->title('Submitted Successfully')->send();
            })->visible(fn()=>auth()->user()->can('logo_and_name_visitor::request'))
        ];
    }
}
