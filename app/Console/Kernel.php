<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * تنظیمات دستورات زمان‌بندی.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // دستور زمان‌بندی شما
        $schedule->call(function () {
            \Illuminate\Support\Facades\Artisan::call('app:update-currency');
        })->everyFiveMinutes(); // اجرای هر ۵ دقیقه
    }

    /**
     * تنظیمات دستورات کنسولی.
     *
     * @return void
     */
    protected function commands()
    {
        // بارگذاری دستورات کنسولی
        $this->load(__DIR__.'/Commands');
        dd(1);
        // بارگذاری دستورات از فایل console.php
        require base_path('routes/console.php');
    }
}
