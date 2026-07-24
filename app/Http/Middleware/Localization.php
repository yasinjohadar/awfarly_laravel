<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //set language code
        if (Auth::guard('admin')->check()) {
            if (!Session::has('userLocale')) {
                Session::put('userLocale', Auth::guard('admin')->user()->language_code);
            } else if (Session::get('userLocale', 'ar') !== Auth::guard('admin')->user()->language_code) {
                Session::put('userLocale', Auth::guard('admin')->user()->language_code);
            }
        }

        $language = Session::get('userLocale', 'ar');
        //set app locale
        App::setlocale($language);

        return $next($request);
    }
}
