<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\CEOapproval as WidgetsCEOapproval;
use Filament\Pages\Page;

class CEOapproval extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = "Approval request";

    protected static string $view = 'filament.admin.pages.c-e-oapproval';

    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsCEOapproval::class,
        ];
    }
}
