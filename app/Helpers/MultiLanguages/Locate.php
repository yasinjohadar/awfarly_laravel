<?php

namespace App\Helpers\MultiLanguages;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use App;
use Config;

class Locate
{
    /*
    * Set locate
    */
    public static function Set($locale = null)
    {
        if ($locale === null) {
            $locale = Cookie::get('locate');
        }

        if (in_array($locale, Config::get('app.locales'))) {
            Cookie::queue('locate', $locale, 525600);
        } else {
            $locale = Config::get('app.locale');
        }

        App::setLocale($locale);
    }

    /*
    * Get locate
    */
    public static function Get()
    {
        $locale = Cookie::get('locate');

        if (!in_array($locale, Config::get('app.locales'))) {
            $locale = Config::get('app.locale');
        }

        return $locale;
    }
}
