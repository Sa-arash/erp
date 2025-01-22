<?php

namespace App\Filament\Admin\Resources\PayrollResource\Pages;

use App\Filament\Admin\Resources\PayrollResource;
use App\Models\Bank_category;
use App\Models\Expense;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;
    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(fn () =>   !$this->data['calculation_done']  ?  "Please Click On Calculate" : "Create"  )
            ->submit('create')->disabled(fn () => !$this->data['calculation_done'])
            ->keyBindings(['mod+s']);
    }



}
