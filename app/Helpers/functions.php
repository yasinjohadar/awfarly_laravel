<?php

if (!function_exists('array_merge_recursive_distinct')) {
    /**
     * Recursively merge arrays, overwriting scalar values from later arrays.
     *
     * @param array ...$arrays
     * @return array
     */
    function array_merge_recursive_distinct(array ...$arrays): array
    {
        $base = array_shift($arrays) ?? [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (
                    is_array($value) &&
                    isset($base[$key]) &&
                    is_array($base[$key])
                ) {
                    $base[$key] = array_merge_recursive_distinct($base[$key], $value);
                } else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }
}
