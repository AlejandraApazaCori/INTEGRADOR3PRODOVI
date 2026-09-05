<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('database-backups:run-due')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('meta:sync-training-history')->hourly()->withoutOverlapping();
Schedule::command('publicaciones:procesar-programadas')->everyMinute()->withoutOverlapping();
