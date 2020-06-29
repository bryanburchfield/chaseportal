<?php

namespace App\Traits;

use App\Models\System;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

trait TimeTraits
{

    private function timezones()
    {
        $timezone_array = ['' => trans('general.select_one')];

        // Get US timezones first
        $timezones = System::whereIn(
            'name',
            [
                'Eastern Standard Time',
                'Central Standard Time',
                'Mountain Standard Time',
                'Pacific Standard Time',
                'Alaskan Standard Time',
                'Hawaiian Standard Time',
            ]
        )
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // Now UTC for the UK
        $timezones = System::whereIn(
            'name',
            [
                'Greenwich Standard Time',
            ]
        )
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // And Australia
        $timezones = System::whereIn(
            'name',
            [
                'W. Australia Standard Time',
                'Aus Central W. Standard Time',
                'AUS Central Standard Time',
                'E. Australia Standard Time',
                'Cen. Australia Standard Time',
                'AUS Eastern Standard Time',
            ]
        )
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // And then the rest
        $timezones = System::whereNotIn(
            'name',
            [
                'Eastern Standard Time',
                'Central Standard Time',
                'Mountain Standard Time',
                'Pacific Standard Time',
                'Alaskan Standard Time',
                'Hawaiian Standard Time',
                'Greenwich Standard Time',
                'W. Australia Standard Time',
                'Aus Central W. Standard Time',
                'AUS Central Standard Time',
                'E. Australia Standard Time',
                'Cen. Australia Standard Time',
                'AUS Eastern Standard Time',
            ]
        )
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        return $timezone_array;
    }

    public function abbrToText($abbr)
    {
        $timezones = [
            'AST' => 'Atlantic Standard Time',
            'EST' => 'Eastern Standard Time',
            'EDT' => 'Eastern Daylight Time',
            'CST' => 'Central Standard Time',
            'CDT' => 'Central Daylight Time',
            'MST' => 'Mountain Standard Time',
            'MDT' => 'Mountain Daylight Time',
            'PST' => 'Pacific Standard Time',
            'PDT' => 'Pacific Daylight Time',
            'AKST' => 'Alaska Time',
            'AKDT' => 'Alaska Daylight Time',
            'HST' => 'Hawaii Standard Time',
            'HAST' => 'Hawaii-Aleutian Standard Time',
            'HADT' => 'Hawaii-Aleutian Daylight Time',
            'SST' => 'Samoa Standard Time',
            'SDT' => 'Samoa Daylight Time',
            'CHST' => 'Chamorro Standard Time',
        ];

        if (!empty($timezones[$abbr])) {
            return $timezones[$abbr];
        }

        return '';
    }

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

    public function stringToUtc($datestring, $tz, $dateonly = false)
    {
        if ($dateonly) {
            return Carbon::createFromIsoFormat('L', $datestring, $tz, App::getLocale())->modify('midnight')->tz('UTC');
        }
        return Carbon::createFromIsoFormat('L LT', $datestring, $tz, App::getLocale())->tz('UTC');
    }
}
