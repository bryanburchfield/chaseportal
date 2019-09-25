<?php

if (!function_exists('windowsToUnixTz')) {
    function windowsToUnixTz($windowsTz)
    {
        return \IntlTimeZone::getIDForWindowsID($windowsTz);
    }
}

if (!function_exists('localToUtc')) {
    function localToUtc($datetime, $ianaTz)
    {
        if (is_a($datetime, '\DateTime')) {
            $datetime = $datetime->format('Y-m-d H:i:s');
        }

        $dt = new \DateTime($datetime, new \DateTimeZone($ianaTz));
        $dt->setTimeZone(new \DateTimeZone('UTC'));

        return $dt;
    }
}

if (!function_exists('utcToLocal')) {
    function utcToLocal($datetime, $ianaTz)
    {
        if (is_a($datetime, '\DateTime')) {
            $datetime = $datetime->format('Y-m-d H:i:s');
        }

        $dt = new \DateTime($datetime, new \DateTimeZone('UTC'));
        $dt->setTimeZone(new \DateTimeZone($ianaTz));

        return $dt;
    }
}

if (!function_exists('secondsToHms')) {
    function secondsToHms($secs)
    {
        $secs = round($secs);

        $hours = floor($secs / 3600);
        $minutes = floor(($secs / 60) % 60);
        $seconds = $secs % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}

if (!function_exists('deleteColumn')) {
    function deleteColumn($array, $key)
    {
        array_walk($array, function (&$v) use ($key) {
            unset($v[$key]);
        });
        return $array;
    }
}

if (!function_exists('makeDateTime')) {
    function makeDateTime($datetime)
    {
        if (!is_a($datetime, '\DateTime')) {
            $datetime = new \DateTime($datetime);
        }
        return $datetime;
    }
}
