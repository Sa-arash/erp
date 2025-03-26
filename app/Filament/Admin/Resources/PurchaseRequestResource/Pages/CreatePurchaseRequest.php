<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;
    public function afterCreate(){
        $request=$this->record;
        sendApprove($request,'PR Warehouse/Storage Clarification_approval');


    }
}
