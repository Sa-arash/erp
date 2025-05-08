<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());


})->purpose('Display an inspiring quote')->everyFiveMinutes();
Artisan::command('app:update-currency', function () {
    $this->info('Currency updated!');
})->describe('Update the currency.');
