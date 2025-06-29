<?php

namespace App\Listeners;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\ActivityLogStatus;

class LogLogoutActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event)
    {

        $login = DB::table('activity_log')
            ->where('causer_id', $event->user->id)
            ->where('event','Login')
            ->orderBy('created_at','desc')
            ->first();
            if ($login->event === 'Login') {
                $loginTime = Carbon::parse($login->created_at);
                $logoutTime = Carbon::parse(now());
                $duration = $loginTime->diffForHumans($logoutTime, true);
                $description = Filament::getUserName($event->user).'Logout';
                app(ActivityLogger::class)
                    ->useLog(config('filament-logger.access.log_name'))
                    ->setLogStatus(app(ActivityLogStatus::class))
                    ->withProperties([
                        'login_time' => $loginTime->toDateTimeString(),
                        'logout_time' => $logoutTime->toDateTimeString(),
                        'duration' => $duration,
                    ])
                    ->event('Logout')
                    ->log($description);

            }
        }
}
