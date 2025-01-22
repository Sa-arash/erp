<?php

namespace App\Filament\Admin\Resources\BillResource\Pages;

use App\Filament\Admin\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBill extends CreateRecord
{
    protected static string $resource = BillResource::class;
}
