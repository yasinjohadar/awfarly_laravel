<?php

namespace App\Providers;

use App\Models\Languages\Language;
use App\Models\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Resolve current UI language with safe fallbacks when seed data is missing.
     */
    public static function resolveUserLanguage(?string $code = null): ?Language
    {
        $code = $code ?: Session::get('userLocale', App::getLocale() ?: 'ar');

        return Language::where('code', $code)->first()
            ?? Language::where('is_default', true)->first()
            ?? Language::first();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //round diffForHumans() to the nearest unit (e.g. 5d 23h -> "6 days ago") instead of truncating down
        Carbon::enableHumanDiffOption(Carbon::ROUND);

        //sidebar current active tab
        view()->composer(
            'admin.includes.sidebar',
            static function ($view) {
                if (
                    Request::routeIs('admin.admins.*') ||
                    Request::routeIs('admin.roles.*') ||
                    Request::routeIs('admin.customers.*') ||
                    Request::routeIs('admin.advertisers.*')
                ) {
                    $alias = 'users';
                } else if (Request::routeIs('admin.categories.*')) {
                    $alias = 'categories';
                } else if (Request::routeIs('admin.community.*')) {
                    $alias = 'community';
                } else if (Request::routeIs('admin.subscriptions.*')) {
                    $alias = 'subscriptions';
                } else if (
                    Request::routeIs('admin.countries.*') ||
                    Request::routeIs('admin.governorates.*') ||
                    Request::routeIs('admin.cities.*')
                ) {
                    $alias = 'languages';
                } else if (Request::routeIs('admin.requests.*')) {
                    $alias = 'requests';
                } else if (Request::routeIs('admin.pages.*')) {
                    $alias = 'pages';
                } else if (
                    Request::routeIs('admin.advertisements.*') ||
                    Request::routeIs('admin.slider.advertisements.*') ||
                    Request::routeIs('admin.side.advertisements.*')
                ) {
                    $alias = 'advertisements';
                } else if (Request::routeIs('admin.send.*')) {
                    $alias = 'marketing-tools';
                } else if (
                    Request::routeIs('admin.system.settings.index') ||
                    Request::routeIs('admin.system.logs.index')
                ) {
                    $alias = 'system';
                } else {
                    $alias = 'dashboard';
                }
                $view->with([
                    'alias' => $alias,
                ]);
            }
        );

        //navbar current languages
        view()->composer(
            'admin.includes.navbar',
            static function ($view) {
                $languages = Language::select('name', 'code', 'image', 'direction')
                    ->get();

                $user_language = Auth::guard('admin')->user()
                    ->user_language()
                    ->select('name', 'code', 'image', 'direction')
                    ->first()
                    ?? AppServiceProvider::resolveUserLanguage();

                $view->with([
                    'languages' => $languages,
                    'user_language' => $user_language
                ]);
            }
        );

        //current data for languages, title, about us for frontend.
        view()->composer(
            'auth.includes.navbar',
            static function ($view) {
                $languages = Language::select('name', 'code', 'image', 'direction')
                    ->get();

                $view->with([
                    'languages' => $languages,
                    'user_language' => AppServiceProvider::resolveUserLanguage(),
                ]);
            }
        );

        //current data for languages, title, about us for frontend.
        view()->composer(
            'frontend.includes.header',
            static function ($view) {
                $languages = Language::select('name', 'code', 'image', 'direction')
                    ->get();

                $user_language = AppServiceProvider::resolveUserLanguage();

                //get language column to show
                $title = App::currentLocale() === 'ar' ? 'title_ar' : 'title_en';

                $about_us_page = Page::where('slug', 'like', '%about%')
                    ->first();

                $about_us = $about_us_page ? [
                    'id' => $about_us_page->id,
                    'title' => $about_us_page->{$title},
                    'slug' => $about_us_page->slug,
                ] : null;

                $view->with([
                    'languages' => $languages,
                    'user_language' => $user_language,
                    'about_us' => $about_us,
                ]);
            }
        );

        //current pages for footer
        view()->composer(
            'frontend.includes.footer',
            static function ($view) {
                //get name column to show countries, cities in current user language
                $name_column = (App::currentLocale() === 'ar') ? 'title_ar' : 'title_en';

                $pages = Page::where('is_active', true)
                    ->get()
                    ->map(function ($page) use ($name_column) {
                        return [
                            'id' => $page->id,
                            'slug' => $page->slug,
                            'title' => $page->{$name_column}
                        ];
                    });

                $view->with([
                    'pages' => $pages,
                ]);
            }
        );

        //get user role
        view()->composer(
            'admin.includes.user',
            static function ($view) {
                $user_role = Auth::guard('admin')->user()
                    ->getRoleNames()
                    ->first();

                $view->with([
                    'user_role' => $user_role
                ]);
            }
        );
    }
}
