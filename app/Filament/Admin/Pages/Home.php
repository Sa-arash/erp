<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Home extends Page
{
    public function mount(){
        if (auth()->user()->need_new_password){
            return  redirect(route('filament.admin.auth.profile'));
        }
    }
    protected static ?string $navigationIcon = 'heroicon-s-home';
    protected static ?int $navigationSort=-11;
    protected ?string $heading='';
    protected static string $view = 'filament.admin.pages.home';
}
