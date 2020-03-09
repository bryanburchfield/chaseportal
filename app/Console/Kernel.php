<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ReportController;
use App\Services\CallerIdService;
use App\Services\DemoClientService;
use App\Services\EmailDripService;
use App\Services\LeadMoveService;
use Illuminate\Support\Facades\App;

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
        // Run KPIs
        $schedule->call(function () {
            foreach (KpiController::cronDue() as $rec) {
                KpiController::cronRun($rec);
            }
        })
            ->everyFifteenMinutes()
            ->runInBackground();

        // Run Automated Reports
        $schedule->call(function () {
            foreach (ReportController::cronDue() as $rec) {
                ReportController::cronRun($rec);
            }
        })
            ->dailyAt('6:00')
            ->timezone('America/New_York')
            ->runInBackground();

        // Run Lead Filters
        $schedule->call(function () {
            LeadMoveService::runFilter();
        })
            ->dailyAt('6:30')
            ->runInBackground()
            ->timezone('America/New_York');

        // Expire Demo Users
        $schedule->call(function () {
            DemoClientService::expireDemos();
        })
            ->everyTenMinutes()
            ->runInBackground();

        // Run Email Drip campaigns
        $schedule->call(function () {
            EmailDripService::runDrips();
        })
            ->hourly()
            ->runInBackground();

        // Caller ID Report (production only)
        if (App::environment('production')) {

            // Run for BT only
            $schedule->call(function () {
                CallerIdService::execute(235773);
            })
                ->dailyAt('7:50')
                ->timezone('America/New_York');

            // Run for all groups
            $schedule->call(function () {
                CallerIdService::execute();
            })
                ->dailyAt('8:00')
                ->timezone('America/New_York');
        }
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
