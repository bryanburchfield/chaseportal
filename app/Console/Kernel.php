<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ReportController;
use App\Services\Broadcaster;
use App\Services\CallerIdService;
use App\Services\ContactsPlaybookService;
use App\Services\CustomKpiService;
use App\Services\DemoClientService;
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
        // Run broadcasts
        $schedule->call(function () {
            Broadcaster::run();
        })
            ->everyMinute()
            ->runInBackground();

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

        // Expire Demo Users
        $schedule->call(function () {
            DemoClientService::expireDemos();
        })
            ->everyTenMinutes()
            ->runInBackground();

        // Caller ID Report (production only)
        if (App::environment('production')) {
            $schedule->call(function () {
                CallerIdService::execute();
            })
                ->dailyAt('19:00')
                ->timezone('America/New_York');
        }

        // Custom KPI (production only)
        // if (App::environment('production')) {
        $schedule->call(function () {
            CustomKpiService::group211562();
        })
            ->dailyAt('8:00')
            ->timezone('America/New_York');
        // }

        // Run Contacts Playbooks
        $schedule->call(function () {
            ContactsPlaybookService::execute();
        })
            ->hourly()
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
