<?php

namespace App\Helpers;

use voku\helper\AntiXSS;

class Filter extends Helper
{
    /*
    * Filter Text from Html Tags.
    */
    public static function HtmlFilter($data)
    {
        $filter = function($data){
           return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', false);
        };

        if(is_array($data)){
            return array_map($filter, $data);
        }

        return is_string($data) ? $filter($data) : $data;
    }

    /*
    * Filter Text from special chars.
    */
    public static function SpecialCharsFilter($data)
    {
        // return data if null
        if(is_null($data)){
            return null;
        }

        $filter = function ($data, $replace = ' ') {
            return preg_replace('/[^A-Za-z0-9\s]/', $replace, $data);
        };

        if (is_array($data))
            return array_map($filter, $data);
        return $filter($data);
    }

    /*
    * Filter Text and remove Html Tags.
    */
    public static function RemoveHtml($data)
    {
        // return data if null
        if(is_null($data)){
            return null;
        }

        $filter = function ($data) {
            return strip_tags($data, ENT_QUOTES);
        };

        if (is_array($data))
            return array_map($filter, $data);
        return $filter($data);
    }

    /*
    * Filter XSS injected codes without removing Html Tags.
    */
    public static function RemoveXSS($data)
    {
        $filter = function ($data) {
            return (new AntiXSS())->xss_clean($data);
        };

        if (is_array($data))
            return array_map($filter, $data);
        return $filter($data);
    }

    /*
    * Strip Emails from string
    */
    public static function StripEmails($string)
    {
        $email_pattern = '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i';

        // Match emails and push emails into array $emails
        preg_match_all($email_pattern, $string, $emails);

        $sanitize_emails = function ($emails) {
            // Sanitize each email
            foreach ($emails as $key => $email)
                $emails[$key] = filter_var($email, FILTER_SANITIZE_EMAIL);

            return $emails;
        };

        // Return emails
        return array_map($sanitize_emails, $emails)[0];
    }

    /*
    * Get string between two strings
    */
    public static function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /*
    * Restyle numbers
    */
    public static function RestyleNumbers($number, $format_only = false)
    {
        if ($number == '-') {
            return $number;
        } else {
            intval($number);
        }

        if ($format_only === true) {
            return number_format($number);
        }

        if ($number > 1000) {
            $number = round($number);
            $number_number_format = number_format($number);
            $number_array = explode(',', $number_number_format);
            $number_parts = ['K', 'M', 'B', 'T'];
            $number_count_parts = count($number_array) - 1;
            //$number_display = $number;
            $number_display = $number_array[0] . ((int)$number_array[1][0] !== 0 ? '.' . $number_array[1][0] : '');
            $number_display .= $number_parts[$number_count_parts - 1];

            return $number_display;
        }

        return $number;
    }

    /*
    * Restyle file size
    */
    public static function RestyleFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /*
    * Restyle time
    */
    public static function RestyleTime($time)
    {
        switch ($time) {
            case ($time > 0 && $time < 60):
                $time = ceil($time);
                $time .= 'M';
                break;
            case ($time >= 1400):
                $time = ceil($time / 1440);
                $time .= 'D';
                break;
            case ($time >= 60):
                $time = ceil($time / 60);
                $time .= 'H';
                break;
            default:
                $time = '0M';
        }

        return $time;
    }

    //Remove null keys from array
    public static function RemoveArrayNullKeys($array, $callback = '')
    {
        foreach ($array as $key => & $value) {
            if (is_array($value)) {
                $value = self::array_filter_recursive($value, $callback);
            } else {
                if (!empty($callback)) {
                    if (!$callback($value)) {
                        unset($array[$key]);
                    }
                } else {
                    if (!(bool)$value) {
                        unset($array[$key]);
                    }
                }
            }
        }
        unset($value);

        return $array;
    }

    //GetHostFromURL
    public static function GetHostFromURL($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }
}
