<?php

namespace App\Traits;

use Cron\CronExpression;
use Illuminate\Support\Carbon;

trait Schedulable
{
    public function isDue($expression, $timezone = null)
    {
        $date = Carbon::now();

        if ($timezone) {
            $date->setTimezone($timezone);
        }

        return CronExpression::factory($expression)->isDue($date->toDateTimeString());
    }

    // public function nextDue($expression)
    // {
    //     return Carbon::instance(CronExpression::factory($expression)->getNextRunDate());
    // }

    // public function lastDue($expression)
    // {
    //     return Carbon::instance(CronExpression::factory($expression)->getPreviousRunDate());
    // }
}
