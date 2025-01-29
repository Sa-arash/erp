<?php

namespace App\Filament\Admin\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class TakeOut extends Page
{
    use HasPageShield;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.take-out';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\TakeOut::class
        ];
    }

//    public static function getNavigationBadge():?string
//    {
//
//        return \App\Models\TakeOut::query()->where('head_department_id',null)->where('employee_id', getEmployee()->id)->count();
//    }
}
