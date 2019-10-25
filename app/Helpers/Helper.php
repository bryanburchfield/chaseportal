<?php

if (!function_exists('deleteColumn')) {
    function deleteColumn($array, $key)
    {
        array_walk($array, function (&$v) use ($key) {
            unset($v[$key]);
        });
        return $array;
    }
}

if (!function_exists('resultsToList')) {
    function resultsToList($results)
    {
        // flatten array, create k=>v pairs
        if (count($results)) {
            $arr = [];
            if (count($results[0]) == 1) {
                $key = implode('', array_keys($results[0]));
                $results = array_column($results, $key);
                foreach ($results as $v) {
                    $arr[$v] = $v;
                }
            } elseif (count($results[0]) == 2) {
                foreach ($results as $rec) {
                    $vals = array_values($rec);
                    $arr[$vals[0]] = $vals[1];
                }
            }
            $results = $arr;
        }
        return $results;
    }
}
