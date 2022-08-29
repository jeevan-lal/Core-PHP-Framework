<?php

/**
 * Get Nice Date
 */
if (!function_exists('get_nice_date')) {
    function get_nice_date($timestamp, $format)
    {
        if (empty($timestamp))
            return false;

        switch ($format) {
            case 'full':
                // Full // Friday 18th of February 2016 at 10:00:00 AM
                $the_date  = date('l jS \of F Y \a\t h:i:s A', $timestamp);
                break;

            case 'cool':
                // Full // Friday 18th of February 2016
                $the_date  = date('l jS \of F Y', $timestamp);
                break;

            case 'shorter':
                // Full // 18th of February 2016
                $the_date  = date('jS \of F Y', $timestamp);
                break;

            case 'mini':
                // Full // 18th Feb 2016
                $the_date  = date('jS\ M Y', $timestamp);
                break;

            case 'oldschool':
                // Full // 18/2/11
                $the_date  = date('j\/n\/y', $timestamp);
                break;

            case 'datepicker':
                // Full // 18/2/11
                $the_date  = date('d/-m/-Y', $timestamp);
                break;

            case 'monyear':
                // Full // 18th Feb 2016
                $the_date  = date('F Y', $timestamp);
                break;

            case 'fullDateTime':
                $the_date  = date('d/m/Y h:i:s A', $timestamp);
                break;
        }
        return $the_date;
    }
}

/*
  Want to share php function which results in grammatically correct Facebook like human readable time format.
  Result: less than 1 minute ago
*/
if (!function_exists('get_time_ago')) {
    function get_time_ago($time_stamp)
    {
        if (empty($time_stamp))
            return false;

        function get_time_ago_string($time_stamp, $divisor, $time_unit)
        {
            $time_difference = strtotime("now") - $time_stamp;
            $time_units      = floor($time_difference / $divisor);

            settype($time_units, 'string');

            if ($time_units === '0') {
                return 'less than 1 ' . $time_unit . ' ago';
            } elseif ($time_units === '1') {
                return '1 ' . $time_unit . ' ago';
            } else {
                /*
                    * More than "1" $time_unit. This is the "plural" message.
                    */
                // TODO: This pluralizes the time unit, which is done by adding "s" at the end; this will not work for i18n!
                return $time_units . ' ' . $time_unit . 's ago';
            }
        }

        $time_difference = strtotime('now') - $time_stamp;

        if ($time_difference >= 60 * 60 * 24 * 365.242199) {
            /*
                * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 365.242199 days/year
                * This means that the time difference is 1 year or more
                */
            return get_time_ago_string($time_stamp, 60 * 60 * 24 * 365.242199, 'year');
        } elseif ($time_difference >= 60 * 60 * 24 * 30.4368499) {
            /*
                * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 30.4368499 days/month
                * This means that the time difference is 1 month or more
                */
            return get_time_ago_string($time_stamp, 60 * 60 * 24 * 30.4368499, 'month');
        } elseif ($time_difference >= 60 * 60 * 24 * 7) {
            /*
                * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 7 days/week
                * This means that the time difference is 1 week or more
                */
            return get_time_ago_string($time_stamp, 60 * 60 * 24 * 7, 'week');
        } elseif ($time_difference >= 60 * 60 * 24) {
            /*
                * 60 seconds/minute * 60 minutes/hour * 24 hours/day
                * This means that the time difference is 1 day or more
                */
            return get_time_ago_string($time_stamp, 60 * 60 * 24, 'day');
        } elseif ($time_difference >= 60 * 60) {
            /*
                * 60 seconds/minute * 60 minutes/hour
                * This means that the time difference is 1 hour or more
                */
            return get_time_ago_string($time_stamp, 60 * 60, 'hour');
        } else {
            /*
                * 60 seconds/minute
                * This means that the time difference is a matter of minutes
                */
            return get_time_ago_string($time_stamp, 60, 'minute');
        }
    }
}
