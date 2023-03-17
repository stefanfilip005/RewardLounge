<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sanctum:prune-expired --hours=12')->daily();

        // $schedule->command('inspire')->hourly();
        $schedule->command('command:grabEmployees')->dailyAt('02:05');
        $schedule->command('command:grabShifts')->dailyAt('02:10');
        $schedule->command('command:calculatePoints')->dailyAt('02:15');
        $schedule->command('command:calculateRanking')->dailyAt('02:25');
        $schedule->command('command:calculateRankingDistribution')->dailyAt('02:30');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
