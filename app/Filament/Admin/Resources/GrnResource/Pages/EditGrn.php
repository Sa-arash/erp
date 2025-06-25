<?php

namespace App\Filament\Admin\Resources\GrnResource\Pages;

use App\Filament\Admin\Resources\GrnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrn extends EditRecord
{
    protected static string $resource = GrnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
    public static function canAccess(array $parameters = []): bool
    {

        return $parameters['record']->items()->where('receive_status','!=','Approved')->count() ===0;

    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();


//         $this->data['']=$this->record->purchaseRequest?->purchase_number;

        $this->previousUrl = url()->previous();
    }
}
