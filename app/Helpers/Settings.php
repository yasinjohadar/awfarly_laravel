<?php

namespace App\Helpers;

use App\Models\Settings\Setting;
use Illuminate\Support\Str;

class Settings extends Helper
{
    /**
     * Get Settings
     * @param null $key
     * @param null $value initial value
     * @param bool $raw send data raw
     * @return bool|float|int|mixed|null
     */
    public static function Get($key = null, $value = null, $raw = false)
    {
        $setting = Setting::where('key', $key)
            ->first();

        //If send row
        if ($raw) {
            return $setting;
        }

        //If no results
        if (!$setting) {
            return $value;
        }

        //Prepare value depending on value type
        $value = self::HandleSettingType($setting->value, $setting->value_type);

        return $value;
    }

    /*
    * Get Settings by type
    */
    public static function GetType($type)
    {
        $settings_raw = Setting::where('type', $type)
            ->get();

        //If no results
        if (!$settings_raw) {
            return null;
        }

        //Set settings to array
        $settings_raw = $settings_raw->toArray();

        //Set settings
        $settings = [];

        //Prepare value depending on value type ad set $settings
        foreach ($settings_raw as $id => $setting) {
            $settings_raw[$id] = [
                "id" => $setting['id'],
                "name" => $setting['name'],
                "key" => $setting['key'],
                "value" => self::HandleSettingType($setting['value'], $setting['value_type']),
                "value_type" => $setting['value_type'],
                "type" => $setting['type'],
            ];

            //Set settings
            $settings[$setting['key']] = $setting['value'];
        }

        return $settings;
    }

    /**
     * Prepare value depending on value type
     * @param $value
     * @param string $type
     * @return bool|float|int|mixed
     */
    public static function HandleSettingType($value, $type = "string")
    {
        //Prepare value depending on value type
        switch ($type) {
            case 'integer':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'boolean':
                $value = boolval($value);
                break;
            case 'array':
                $value = json_decode($value);
                break;
            default:
                $value = strval($value);
        }

        return $value;
    }

    /**
     * Absolute URL for the site logo (admin + frontend).
     *
     * @param string $fallback
     * @return string
     */
    public static function Logo($fallback = 'assets/images/logo_light.png')
    {
        $path = self::Get('site.logo');

        if (!$path) {
            return '/' . ltrim($fallback, '/');
        }

        if (is_string($path) && Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Uploaded logos live on the local disk (storage/app) and are served via route
        if (is_string($path) && Str::startsWith($path, 'uploads/')) {
            return '/image/' . $path;
        }

        return '/' . ltrim($path, '/');
    }
}
