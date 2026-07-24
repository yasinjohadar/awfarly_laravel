<?php

namespace App\Helpers;

class Strings extends Helper
{
    /*
    * Create random md5
    */
    public static function RanMd5()
    {

        return md5(now());
    }

    /**
     * Filter array and remove nulls
     * @param $array
     * @param string $callback
     * @return mixed
     */
    public static function arrayFilterRecursive($array, $callback = '')
    {
        foreach ($array as $key => & $value) {
            if (is_array($value)) {
                $value = self::arrayFilterRecursive($value, $callback);
            } else {
                if (!empty($callback)) {
                    if (!$callback($value)) {
                        unset($array[$key]);
                    }
                } else {
                    if ((is_string($value) and !(bool)$value) or is_null($value)) {
                        unset($array[$key]);
                    }
                }
            }
        }
        unset($value);

        return $array;
    }
}
