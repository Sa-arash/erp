<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Chat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';
    public function getTitle(): string|Htmlable
    {
        return '';
    }

    protected static string $view = 'filament.admin.pages.chat';
}
