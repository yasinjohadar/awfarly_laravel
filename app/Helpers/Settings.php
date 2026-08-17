<?php

namespace App\Helpers;

use App\Models\Settings\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Settings extends Helper
{
    /**
     * Resolved logo values, memoized per request (the logo is asked for many
     * times per page: navbar, footer, meta tags and every missing image).
     * @var array
     */
    protected static $logoCache = [];

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
        if (array_key_exists('url:' . $fallback, self::$logoCache)) {
            return self::$logoCache['url:' . $fallback];
        }

        $path = self::LogoPath();

        if (!$path) {
            $url = '/' . ltrim($fallback, '/');
        } elseif (Str::startsWith($path, ['http://', 'https://'])) {
            $url = $path;
        } elseif (Str::startsWith($path, 'uploads/')) {
            // Uploaded logos live on the local disk (storage/app) and are served via route
            $url = '/image/' . $path;
        } else {
            $url = '/' . ltrim($path, '/');
        }

        return self::$logoCache['url:' . $fallback] = $url;
    }

    /**
     * Raw `site.logo` setting value, memoized per request.
     *
     * @return string|null
     */
    public static function LogoPath()
    {
        if (!array_key_exists('path', self::$logoCache)) {
            $path = self::Get('site.logo');
            self::$logoCache['path'] = (is_string($path) && $path !== '') ? $path : null;
        }

        return self::$logoCache['path'];
    }

    /**
     * The site logo as a source Intervention Image can render: either the raw
     * file contents (uploaded logos live on the local disk) or an absolute
     * path on the public disk. Used as the default image whenever a category,
     * a post owner or an advertiser has no image of its own.
     *
     * @param string $fallback
     * @return string
     */
    public static function LogoImage($fallback = 'assets/images/logo_light.png')
    {
        $path = self::LogoPath();

        // Remote logos are not fetched here, an outbound request per served
        // image would be far too costly, so they fall back to the local file.
        if ($path && !Str::startsWith($path, ['http://', 'https://'])) {
            if (Str::startsWith($path, 'uploads/')) {
                if (Storage::exists($path)) {
                    return Storage::get($path);
                }
            } else {
                $file = public_path(ltrim($path, '/'));

                if (File::exists($file)) {
                    return $file;
                }
            }
        }

        return public_path(ltrim($fallback, '/'));
    }
}
