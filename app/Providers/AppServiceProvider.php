<?php

namespace App\Providers;


use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        FilamentAsset::register([
            Css::make('custom-stylesheet', __DIR__ . '/../../resources/css/custom.css'),
//            Js::make('echo-reverb', asset('/build/assets/app-DuJ78BcV.js')),
            Js::make('echo-reverb', asset('/js/app/app.js')),
        ]);
    }
}
