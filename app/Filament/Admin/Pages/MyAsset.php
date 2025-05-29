<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\MyAsset as WidgetsMyAsset;
use App\Models\AssetEmployeeItem;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MyAsset extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-c-cube';
    protected static ?string $navigationLabel = "My Assets";

    public static function getNavigationBadge(): ?string
    {
        $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
            ->whereHas('assetEmployee', function ($q) {
                $q->where('employee_id', getEmployee()->id);
            })
            ->groupBy('asset_id');

        return AssetEmployeeItem::query()
            ->whereIn('id', $sub)
            ->where('type', 'Assigned')->count();

    }

    protected static string $view = 'filament.admin.pages.my-asset';

    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyAsset::class,
        ];
    }
}
