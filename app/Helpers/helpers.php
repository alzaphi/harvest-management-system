<?php

if (!function_exists('format_date')) {
    /**
     * Format a date
     *
     * @param  string|null  $date
     * @param  string  $format
     * @return string
     */
    function format_date($date = null, $format = 'd/m/Y')
    {
        if (empty($date)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format a datetime
     *
     * @param  string|null  $datetime
     * @param  string  $format
     * @return string
     */
    function format_datetime($datetime = null, $format = 'd/m/Y H:i:s')
    {
        return format_date($datetime, $format);
    }
}

if (!function_exists('format_number')) {
    /**
     * Format a number
     *
     * @param  float|int  $number
     * @param  int  $decimals
     * @return string
     */
    function format_number($number, $decimals = 0)
    {
        return number_format($number, $decimals, ',', '.');
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a currency value
     *
     * @param  float|int  $amount
     * @param  string  $prefix
     * @return string
     */
    function format_currency($amount, $prefix = 'Rp ')
    {
        return $prefix . format_number($amount, 0);
    }
}
