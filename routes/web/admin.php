<?php

use Spatie\Image\Image;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admins\Pages\PagesController;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Controllers\Admins\Admins\AdminsController;
use App\Http\Controllers\Admins\Account\AccountController;
use App\Http\Controllers\Admins\Countries\CountriesController;
use App\Http\Controllers\Admins\Customers\CustomersController;
use App\Http\Controllers\Admins\Categories\CategoriesController;
use App\Http\Controllers\Admins\System\Logs\SystemLogsController;
use App\Http\Controllers\Admins\Advertisers\AdvertisersController;
use App\Http\Controllers\Admins\Admins\Roles\AdminsRolesController;
use App\Http\Controllers\Admins\Dashboard\AdminDashboardController;
use App\Http\Controllers\Admins\Requests\ContactUsRequestsController;
use App\Http\Controllers\Admins\Advertisements\AdvertisementsController;
use App\Http\Controllers\Admins\Community\Chats\CommunityChatsController;
use App\Http\Controllers\Admins\Community\Posts\CommunityPostsController;
use App\Http\Controllers\Admins\System\Settings\SystemSettingsController;
use App\Http\Controllers\Admins\Requests\UsernameChangeRequestsController;
use App\Http\Controllers\Admins\Community\Offers\CommunityOffersController;
use App\Http\Controllers\Admins\Countries\Cities\CountriesCitiesController;
use App\Http\Controllers\Admins\Countries\Governorates\CountriesGovernoratesController;
use App\Http\Controllers\Admins\Community\Comments\CommunityCommentsController;
use App\Http\Controllers\Admins\MarketingTools\SMS\MarketingToolsSMSController;
use App\Http\Controllers\Admins\Community\Proposals\CommunityProposalsController;
use App\Http\Controllers\Admins\Advertisers\BusinessTypes\BusinessTypesController;
use App\Http\Controllers\Admins\MarketingTools\Modals\MarketingToolsModalController;
use App\Http\Controllers\Admins\MarketingTools\Emails\MarketingToolsEmailsController;
use App\Http\Controllers\Admins\Subscriptions\Packages\SubscriptionsPackagesController;
use App\Http\Controllers\Admins\Subscriptions\Payments\SubscriptionsPaymentsController;
use App\Http\Controllers\Admins\Advertisements\Comments\AdvertisementsCommentsController;
use App\Http\Controllers\Admins\Community\Offers\Comments\CommunityOffersCommentsController;
use App\Http\Controllers\Admins\MarketingTools\Notifications\MarketingToolsNotificationsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('images', function () {
    $media = Media::all();
    foreach ($media as $file) {
        //get file mime type
        $mime_type = $file->mime_type;

        if (strstr($mime_type, "video/")) {
            $file_width = null;
            $file_height = null;
        } else if (strstr($mime_type, "image/")) {
            $file_width = Image::load($file->getUrl())->getWidth();
            $file_height = Image::load($file->getUrl())->getHeight();
        } else if (strstr($mime_type, "audio/")) {
            $file_width = null;
            $file_height = null;
        } else {
            $file_width = null;
            $file_height = null;
        }
        $file->setCustomProperty('width', $file_width)->setCustomProperty('height', $file_height)->save();
    }
});*/
// Dashboard
Route::get('/', [AdminDashboardController::class, 'index'])
    ->name('dashboard');

/**
 * Account
 */
Route::get('/account', [AccountController::class, 'edit'])
    ->name('account.edit');

Route::get('/account/change/language/{language}', [AccountController::class, 'changeLanguage'])
    ->name('language.change');

/**
 * Admins Users
 */
Route::resource('/admins', AdminsController::class)
    ->only([
        'index',
        'create'
    ]);

/**
 * Advertisers Users
 */
//ratings
Route::get('/advertisers/ratings', [AdvertisersController::class, 'getRatings'])
    ->name('advertisers.ratings');

//reported advertisers
Route::get('/advertisers/reports', [AdvertisersController::class, 'reportedAdvertisers'])
    ->name('advertisers.reports');


Route::resource('/advertisers', AdvertisersController::class)
    ->only([
        'index',
        'create',
        'show'
    ]);


// Business Types
Route::resource('/advertisers/business/types', BusinessTypesController::class, [
    'as' => 'advertisers.business',
])
    ->only([
        'index',
        'create'
    ]);

/**
 * Customers Users
 */
//reported advertisers
Route::get('/customers/reports', [CustomersController::class, 'reportedCustomers'])
    ->name('customers.reports');

Route::resource('/customers', CustomersController::class)
    ->only([
        'index',
        'show',
        'create'
    ]);

/**
 * Admins Roles
 */
Route::resource('/roles', AdminsRolesController::class)
    ->except([
        'destroy',
        'show'
    ]);


/**
 * Show Admins Logs
 */
Route::group([
    'prefix' => 'system',
    'as' => 'system.',
], function () {
    //posts controller
    Route::resource('/logs', SystemLogsController::class)
        ->only([
            'index'
        ]);

    Route::get('settings/{type?}', [SystemSettingsController::class, 'index'])
        ->name('settings.index');
});


/**
 * Show Admins Logs
 */
Route::resource('/categories', CategoriesController::class)
    ->only([
        'index',
        'create'
    ]);

/**
 * Community routes
 */

Route::group([
    'prefix' => 'community',
    'as' => 'community.',
], function () {
    //posts controller
    Route::get('/posts/reports', [CommunityPostsController::class, 'reportedPosts'])
        ->name('posts.reports');

    Route::resource('/posts', CommunityPostsController::class)
        ->only([
            'index',
            'show'
        ]);

    //comments controller
    Route::get('/comments/reports', [CommunityCommentsController::class, 'reportedComments'])
        ->name('comments.reports');

    Route::resource('/comments', CommunityCommentsController::class)
        ->only([
            'index',
            'show'
        ]);

    Route::group([
        'as' => 'offers.',
        'prefix' => 'offers'
    ], function () {

        Route::get('/', [CommunityOffersController::class, 'index'])
            ->name('index');

        Route::get('/reports', [CommunityOffersController::class, 'reportedOffers'])
            ->name('reports');

        Route::get('/ratings', [CommunityOffersController::class, 'getRatings'])
            ->name('ratings');

        //comments controller
        Route::get('/comments/reports', [CommunityOffersCommentsController::class, 'reportedComments'])
            ->name('comments.reports');

        Route::resource('/comments', CommunityOffersCommentsController::class)
            ->only([
                'index',
                'show'
            ]);
        Route::get('/{id}', [CommunityOffersController::class, 'show'])
            ->name('show');
    });

    Route::get('/proposals/reports', [CommunityProposalsController::class, 'reportedProposals'])
        ->name('proposals.reports');

    //proposals controller
    Route::resource('/proposals', CommunityProposalsController::class)
        ->only([
            'index',
            'show',
        ]);

    //chats controller
    Route::resource('/chats', CommunityChatsController::class)
        ->only([
            'index',
        ]);

    Route::group([
        'prefix' => 'reports',
        'as' => 'reports.',
    ], function () {
        Route::get('/posts', [CommunityPostsController::class, 'reportedPosts'])
            ->name('posts');

        Route::get('/posts/comments', [CommunityCommentsController::class, 'reportedComments'])
            ->name('comments');

        Route::get('/offers', [CommunityOffersController::class, 'reportedOffers'])
            ->name('offers');

        Route::get('/proposals', [CommunityProposalsController::class, 'reportedProposals'])
            ->name('proposals');
    });
});

/**
 * Countries routes
 */
Route::resource('countries', CountriesController::class)
    ->only([
        'index',
        'create',
    ]);

/**
 * Governorates routes
 */
Route::resource('governorates', CountriesGovernoratesController::class)
    ->only([
        'index',
        'create',
    ]);

Route::get('governorates/get', [CountriesGovernoratesController::class, 'getGovernoratesByCountryCode'])
    ->name('country.governorates');

/**
 * Cities routes
 */
Route::resource('cities', CountriesCitiesController::class)
    ->only([
        'index',
        'create',
    ]);

Route::get('cities/get', [CountriesCitiesController::class, 'getCitiesByGovernorateId'])
    ->name('governorate.cities');

/**
 * Pages routes
 */
Route::resource('pages', PagesController::class)
    ->only([
        'index',
    ]);


/**
 * Requests routes
 */

Route::group([
    'prefix' => 'requests',
    'as' => 'requests.',
], function () {

    Route::resource('contact-us', ContactUsRequestsController::class)
        ->only([
            'index'
        ]);

    Route::resource('change-name', UsernameChangeRequestsController::class)
        ->only([
            'index'
        ]);
});

/**
 * Send routes
 */
Route::group([
    'prefix' => 'send',
    'as' => 'send.',
], function () {

    Route::resource('notifications', MarketingToolsNotificationsController::class)
        ->only([
            'index'
        ]);

    Route::resource('sms', MarketingToolsSMSController::class)
        ->only([
            'index'
        ]);
    Route::resource('emails', MarketingToolsEmailsController::class)
        ->only([
            'index'
        ]);
    Route::resource('modals', MarketingToolsModalController::class);
});

/**
 * Advertisements routes
 */
Route::resource('advertisements', AdvertisementsController::class)
    ->except([
        'destroy',
        'show',
    ]);

Route::get('advertisements/comments/reports', [AdvertisementsCommentsController::class, 'reportedComments'])
    ->name('advertisements.comments.reports');

Route::resource('advertisements/comments', AdvertisementsCommentsController::class, [
    'as' => 'advertisements',
])
    ->only([
        'index',
        'show'
    ]);

Route::get('advertisements/side', [AdvertisementsController::class, 'getSideAdvertisements'])
    ->name('side.advertisements.index');

Route::get('advertisements/side/create', [AdvertisementsController::class, 'createSideAdvertisements'])
    ->name('side.advertisements.create');

Route::get('advertisements/slider', [AdvertisementsController::class, 'getSliderAdvertisements'])
    ->name('slider.advertisements.index');

Route::get('advertisements/slider/create', [AdvertisementsController::class, 'createSliderAdvertisements'])
    ->name('slider.advertisements.create');
/**
 * Subscriptions routes
 */
Route::group([
    'prefix' => 'subscriptions',
    'as' => 'subscriptions.',
], function () {
    Route::resource('packages', SubscriptionsPackagesController::class)
        ->only([
            'index',
            'show',
            'create'
        ]);

    Route::resource('payments', SubscriptionsPaymentsController::class)
        ->only([
            'index',
        ]);
});
