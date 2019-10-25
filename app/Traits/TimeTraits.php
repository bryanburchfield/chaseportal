<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait TimeTraits
{
    public function windowsToUnixTz($windowsTz)
    {
        return \IntlTimeZone::getIDForWindowsID($windowsTz);
    }

    public function localToUtc($datetime, $ianaTz = null)
    {
        return $this->convertTz($datetime, $ianaTz, 'UTC');
    }

    public function utcToLocal($datetime, $ianaTz = null)
    {
        return $this->convertTz($datetime, 'UTC', $ianaTz);
    }

    private function convertTz($datetime, $fromTz, $toTz)
    {
        // get timezone from logged in user if they didn't give it to us
        if ($fromTz === null) {
            if (Auth::check()) {
                $fromTz = Auth::user()->iana_tz;
            } else {
                throw new \Exception('No timezone specified');
            }
        }
        if ($toTz === null) {
            if (Auth::check()) {
                $toTz = Auth::user()->iana_tz;
            } else {
                throw new \Exception('No timezone specified');
            }
        }

        // figure out what we're working with and create a Carbon object at from tz
        if (is_a($datetime, 'Illuminate\Support\Carbon')) {
            $datetime->tz($fromTz);
        } elseif (is_a($datetime, '\DateTime')) {
            $datetime = new Carbon($datetime->format('Y-m-d H:i:s'), $fromTz);
        } elseif (is_string($datetime)) {
            $datetime = new Carbon($datetime, $fromTz);
        } else {
            throw new \Exception('Unknown format');
        }

        // Convert to target tz
        $datetime->tz($toTz);

        // Return carbon object
        return $datetime;
    }

    public function secondsToHms($secs)
    {
        $secs = round($secs);

        $hours = floor($secs / 3600);
        $minutes = floor(($secs / 60) % 60);
        $seconds = $secs % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}
