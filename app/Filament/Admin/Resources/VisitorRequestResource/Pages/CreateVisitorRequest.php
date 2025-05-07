<?php

namespace App\Filament\Admin\Resources\VisitorRequestResource\Pages;

use App\Filament\Admin\Resources\VisitorRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVisitorRequest extends CreateRecord
{
    protected static string $resource = VisitorRequestResource::class;

    public function afterCreate(){
        sendSecurity($this->record, getCompany());
        // sendAdmin($this->record->employee,$this->record,getCompany());
    }
}
