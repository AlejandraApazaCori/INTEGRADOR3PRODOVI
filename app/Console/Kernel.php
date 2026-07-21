<?php

namespace App\Console;

use App\Console\Commands\ProcessScheduledPublications;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ProcessScheduledPublications::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:run')->dailyAt('01:00');
        $schedule->command('publicaciones:procesar-programadas')->everyMinute()->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
