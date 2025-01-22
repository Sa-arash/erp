<?php

namespace App\Filament\Admin\Resources\AssetEmployeeResource\Pages;

use App\Filament\Admin\Resources\AssetEmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class CreateAssetEmployee extends CreateRecord
{
    protected static string $resource = AssetEmployeeResource::class;


    protected function getRedirectUrl(): string
    {

        return static::getResource()::getUrl('index');
    }

    public function afterCreate(): void
    {
        $this->record->update([
            'approve_date'=>$this->record->date,
            'status'=>"Approve",
        ]);

        foreach ($this->record->assetEmployeeItem as $item) {

            $item->asset->update([
                'warehouse_id' => $item->warehouse_id,
                'structure_id' => $item->structure_id,
                'status' => "inuse",
            ]);
        }

    }


}
