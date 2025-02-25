<?php

namespace App\Filament\Admin\Resources\FactorResource\Pages;

use App\Filament\Admin\Resources\FactorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactor extends EditRecord
{
    protected static string $resource = FactorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();
        foreach ($this->data['invoice']['transactions']  as $key=> $datum){

            if ($datum['cheque']['due_date']){
                $this->data['invoice']['transactions'][$key]['Cheque']=true;
            }
        }

        $this->previousUrl = url()->previous();
    }
}
