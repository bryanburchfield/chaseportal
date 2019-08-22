<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ReportController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            foreach (KpiController::cronDue() as $rec) {
                KpiController::cronRun($rec);
            }
        })
            ->everyMinute()
            ->runInBackground();

        $schedule->call(function () {
            foreach (ReportController::cronDue() as $rec) {
                ReportController::cronRun($rec);
            }
        })
            // ->dailyAt('6:00')
            ->everyMinute()
            ->timezone('America/New_York')
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
