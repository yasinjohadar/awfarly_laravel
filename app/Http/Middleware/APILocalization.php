<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class APILocalization
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
        // Check header request and determine localization
        $language = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : 'ar';

        // set laravel localization
        app()->setLocale($language);
        // continue request
        return $next($request);
    }
}
