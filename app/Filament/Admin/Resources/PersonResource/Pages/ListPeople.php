<?php

namespace App\Filament\Admin\Resources\PersonResource\Pages;

use App\Filament\Admin\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconSize;

class ListPeople extends ListRecords
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Person'),
            Actions\Action::make('print')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->url(fn()=>route('pdf.personals',['id'=>getCompany()->id]))
        ];
    }
}
