<?php

namespace App\Providers;

use App\Listeners\LogLogoutActivity;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
    protected $listen = [
        Logout::class => [
            LogLogoutActivity::class,
        ],
    ];
}
