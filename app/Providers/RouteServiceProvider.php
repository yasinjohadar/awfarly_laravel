<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Web
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web/web.php'));

            Route::prefix('admin')
                ->name('admin.')
                //                ->namespace("$this->namespace\Admins")
                ->middleware(['web', 'auth:admin', 'user'])
                ->group(base_path('routes/web/admin.php'));

            // API
            Route::prefix('api/v1/')
                ->name('api.')
                ->middleware('api')
                //                ->namespace("{$this->namespace}\API")
                ->group(base_path('routes/api/api.php'));

            Route::prefix('api/v1/auth')
                ->name('api.auth.')
                ->middleware('api')
                //                ->namespace("{$this->namespace}\API\Auth")
                ->group(base_path('routes/api/auth.php'));

            Route::prefix('api/v1/guest')
                ->name('api.guest.')
                ->middleware('api')
                //                ->namespace("{$this->namespace}\API\Guests")
                ->group(base_path('routes/api/guest.php'));

            Route::prefix('api/v1/customer')
                ->name('api.customer.')
                ->middleware(['api', 'auth:customer-api', 'user'])
                //                ->namespace("{$this->namespace}\API\Customers")
                ->group(base_path('routes/api/customer.php'));

            Route::prefix('api/v1/advertiser')
                ->name('api.advertiser.')
                ->middleware(['api', 'auth:advertiser-api', 'user'])
                //                ->namespace("{$this->namespace}\API\Advertisers")
                ->group(base_path('routes/api/advertiser.php'));

            Route::middleware(['web', 'user'])
                ->group(base_path('routes/web/user.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
