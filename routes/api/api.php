<?php

use App\Http\Controllers\API\Auth\SocialAuthController;
use App\Http\Controllers\API\Shared\Blockings\UsersBlockingsController;
use App\Http\Controllers\API\Shared\Checks\RegisterDataChecksController;
use App\Http\Controllers\API\Shared\Pages\PagesController;
use App\Http\Controllers\API\Shared\Requests\RequestsController;
use App\Http\Controllers\API\System\BusinessTypes\BusinessTypesController;
use App\Http\Controllers\API\System\Countries\Cities\CitiesController;
use App\Http\Controllers\API\System\Countries\Governorates\GovernoratesController;
use App\Http\Controllers\API\System\Countries\CountriesController;
use App\Http\Controllers\API\Shared\Followings\UsersFollowingsController;
use App\Http\Controllers\API\System\GeoIP\GeoIPController;
use App\Http\Controllers\API\System\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Base
Route::get('/', function () {
    return response('200 OK', 200);
})
    ->name('base');

Route::group([
    'prefix' => 'social',
    'as' => 'social.',
    'middleware' => ['api', 'auth:customer-api,advertiser-api']
], function () {
    Route::post('accounts', [SocialAuthController::class, 'addSocialAccount'])
        ->name('accounts.add');

    Route::delete('accounts', [SocialAuthController::class, 'removeSocialAccount'])
        ->name('accounts.delete');

    Route::get('/accounts', [SocialAuthController::class, 'getSocialAccounts'])
        ->name('accounts.get');
});

/**
 * get Countries
 */
Route::group([
    'prefix' => 'system',
    'as' => 'system.'
], function () {
    //get all countries
    Route::get('/countries', [CountriesController::class, 'getCountries'])
        ->name('countries.get');

    //get country by code
    Route::get('/countries/{code}', [CountriesController::class, 'getCountryByCode'])
        ->name('country.get');

    Route::get('/governorates', [GovernoratesController::class, 'getGovernorates'])
        ->name('governorates.get');

    Route::get('/governorates/{id}', [GovernoratesController::class, 'getGovernorateById'])
        ->name('governorate.get');

    Route::get('/cities', [CitiesController::class, 'getCities'])
        ->name('cities.get');

    Route::get('/cities/{id}', [CitiesController::class, 'getCityById'])
        ->name('city.get');

    //get all business types
    Route::get('/business-types', [BusinessTypesController::class, 'getBusinessTypes'])
        ->name('business.types.get');

    //get GeoIP
    Route::get('/geoip', [GeoIPController::class, 'getGeoIP'])
        ->name('geoip.get');

    // Public site branding (logo, name)
    Route::get('/settings', [SettingsController::class, 'getSettings'])
        ->name('settings.get');
});

Route::group([
    'prefix' => 'users',
    'as' => 'users.',
], function () {
    //follow-unfollow user
    Route::post('/followings', [UsersFollowingsController::class, 'toggleUserFollow'])
        ->name('followings.set')
        ->middleware(['api', 'auth:customer-api,advertiser-api']);

    //get followers
    Route::get('/followers', [UsersFollowingsController::class, 'getFollowers'])
        ->name('followers.get')
        ->middleware(['api', 'auth:customer-api,advertiser-api']);

    //get followed users
    Route::get('/followed', [UsersFollowingsController::class, 'getFollowedUsers'])
        ->name('followed.get')
        ->middleware(['api', 'auth:customer-api,advertiser-api']);

    //get follow requests
    Route::get('/follow/requests', [UsersFollowingsController::class, 'getFollowRequests'])
        ->name('follow.requests.get')
        ->middleware(['api', 'auth:customer-api,advertiser-api']);

    //update follow requests
    Route::put('/follow/requests/{id}', [UsersFollowingsController::class, 'updateFollowRequests'])
        ->name('follow.requests.update')
        ->middleware(['api', 'auth:customer-api,advertiser-api']);

    //check username
    Route::get('/check', [RegisterDataChecksController::class, 'checkData'])
        ->name('data.check');

    Route::post('block', [UsersBlockingsController::class, 'toggleBlock'])
        ->name('block')
        ->middleware(['api', 'auth:customer-api,advertiser-api']);
});

Route::get('/pages', [PagesController::class, 'getAllPages'])
    ->name('pages.get');

Route::get('/pages/{slug}', [PagesController::class, 'getPageBySlug'])
    ->name('pages.get.id');

/**
 * Contact Us
 */
Route::post('/contact-us', [RequestsController::class, 'sendContactForm'])
    ->name('contact.form');
