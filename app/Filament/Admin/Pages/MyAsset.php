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
        return AssetEmployeeItem::query()->where('type', 0)->whereHas('assetEmployee', function ($query) {
            return $query->where('employee_id', auth()->user()->employee->id)->where('type', 'Assigned');
        })->count();
    }

    protected static string $view = 'filament.admin.pages.my-asset';

    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsMyAsset::class,
        ];
    }
}
