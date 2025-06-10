<?php

namespace App\Filament\Admin\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class myEmployees extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';


    protected static string $view = 'filament.admin.pages.my-employees';

    protected static ?string $navigationLabel='My Employees';
   public static function canAccess(): bool
{

        return !(auth()->user()->employee->subordinates->isEmpty());
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\myEmployees::class
        ];
    }


}
