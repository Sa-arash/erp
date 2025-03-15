<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class MyLoan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='My Loans';
    protected static string $view = 'filament.admin.pages.my-loan';
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\MyLoan::class
        ];
    }
}
