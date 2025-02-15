<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\VisitRequest as WidgetsVisitRequest;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class VisitRequest extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = "Visit Requests";

    protected static string $view = 'filament.admin.pages.c-e-oapproval';

    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsVisitRequest::class,
        ];
    }
}
