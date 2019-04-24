<?php

if (!function_exists('localToUtc')) {
    function localToUtc($datetime, $windowsTz)
    {
        if (is_a($datetime, '\DateTime')) {
            $datetime = $datetime->format('Y-m-d H:i:s');
        }

        $ianaTz = \IntlTimeZone::getIDForWindowsID($windowsTz);

        $dt = new \DateTime($datetime, new \DateTimeZone($ianaTz));
        $dt->setTimeZone(new \DateTimeZone('UTC'));

        return $dt;
    }
}

if (!function_exists('utcToLocal')) {
    function utcToLocal($datetime, $windowsTz)
    {
        if (is_a($datetime, '\DateTime')) {
            $datetime = $datetime->format('Y-m-d H:i:s');
        }

        $ianaTz = \IntlTimeZone::getIDForWindowsID($windowsTz);

        $dt = new \DateTime($datetime, new \DateTimeZone('UTC'));
        $dt->setTimeZone(new \DateTimeZone($ianaTz));

        return $dt;
    }
}
