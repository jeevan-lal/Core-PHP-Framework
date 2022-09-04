<?php

/**
 * Truncating Text
 */
if (!function_exists('truncate')) {
    function truncate($string, $length, $end = "...")
    {
        return mb_strimwidth($string, 0, $length, $end);
    }
}
