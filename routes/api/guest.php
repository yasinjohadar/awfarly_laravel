<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Guests\Modals\ModalController;
use App\Http\Controllers\API\Guests\Users\UsersReportsController;
use App\Http\Controllers\API\Guests\Customers\CustomersController;
use App\Http\Controllers\API\Guests\Categories\CategoriesController;
use App\Http\Controllers\API\Guests\Currencies\CurrenciesController;
use App\Http\Controllers\API\Guests\Advertisers\AdvertisersController;
use App\Http\Controllers\API\Guests\Advertisements\AdvertisementsController;
use App\Http\Controllers\API\Guests\Community\Posts\CommunityPostsController;
use App\Http\Controllers\API\Guests\Community\Offers\CommunityOffersController;
use App\Http\Controllers\API\Guests\Community\Comments\CommunityCommentsController;
use App\Http\Controllers\API\Guests\Community\Offers\Comments\CommunityOffersCommentsController;

/*
|--------------------------------------------------------------------------
| Guest API Routes
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


Route::post('/users/report', [UsersReportsController::class, 'reportUser'])
    ->name('users.report');

/**
 * get Categories Routes
 */
Route::get('/categories', [CategoriesController::class, 'getCategories'])
    ->name('categories.get');

Route::get('/categories/{id}', [CategoriesController::class, 'getCategoryById'])
    ->name('category.get');


/**
 * get Currencies Routes
 */
Route::get('/currencies', [CurrenciesController::class, 'getCurrencies'])
    ->name('currencies.get');


/**
 * get Advertisers routes
 */
Route::get('/customers/{id}', [CustomersController::class, 'getCustomerById'])
    ->name('customer.get');
/**
 * get Advertisers routes
 */

//get all advertisers
Route::get('advertisers', [AdvertisersController::class, 'getAdvertisers'])
    ->name('advertisers.get');

//search advertisers
Route::post('advertisers/search', [AdvertisersController::class, 'search'])
    ->name('advertisers.search');

//get elite advertisers
Route::get('advertisers/elite', [AdvertisersController::class, 'getEliteAdvertisers'])
    ->name('advertisers.elite.get');

//get advertiser raters by advertiser id
Route::get('advertisers/{id}/raters', [AdvertisersController::class, 'getRatersByAdvertiserId'])
    ->where('id', '[0-9]+')
    ->name('advertiser.raters');

//get advertiser by id
Route::get('advertisers/{id}', [AdvertisersController::class, 'getAdvertiserById'])
    ->name('advertiser.get');


/**
 * Posts Routes
 */
Route::group([
    'prefix' => 'community',
    'as' => 'community.',
], function () {
    Route::group([
        'prefix' => 'posts',
        'as' => 'posts.'
    ], function () {

        //get all posts
        Route::get('/', [CommunityPostsController::class, 'getAllPosts'])
            ->name('posts.get');

        //search posts
        Route::post('/search', [CommunityPostsController::class, 'search'])
            ->name('posts.search.get');

        //report Post
        Route::post('/reports', [CommunityPostsController::class, 'reportPost'])
            ->name('posts.report.add');

        //get post by id
        Route::get('{id}', [CommunityPostsController::class, 'getPostById'])
            ->where('id', '[0-9]+')
            ->name('post.get');

        //get posts by user id
        Route::get('/advertisers/{user_id}', [CommunityPostsController::class, 'getPostsByUserId'])
            ->name('posts.user.id.get');

        //get posts interacted by customer
        Route::get('/interacted/customer/{user_id}', [CommunityPostsController::class, 'getPostsInteractedByCustomer'])
            ->name('posts.customer.interacted');

        Route::get('{id}/comments', [CommunityCommentsController::class, 'getPostComments'])
            ->name('post.comments.get');

        //report comment
        Route::post('comments/{comment_id}/reports', [CommunityCommentsController::class, 'reportComment'])
            ->name('comments.report.add');
    });



    //Offers
    Route::group([
        'prefix' => 'offers',
        'as' => 'offers.'
    ], function () {

        //get all offers
        Route::get('/', [CommunityOffersController::class, 'getAllOffers'])
            ->name('get');

        //search offers
        Route::post('/search', [CommunityOffersController::class, 'search'])
            ->name('search.get');

        //get offer by id
        Route::get('/{id}', [CommunityOffersController::class, 'getOfferById'])
            ->where('id', '[0-9]+')
            ->name('offer.get');

        //get offers by username
        Route::get('/username/{username}', [CommunityOffersController::class, 'getOffersByUsername'])
            ->name('username.get');

        //report offer
        Route::post('{id}/reports', [CommunityOffersController::class, 'reportOffer'])
            ->name('report.add');

        //comments
        Route::get('{id}/comments', [CommunityOffersCommentsController::class, 'getOfferComments'])
            ->name('comments.get');

        //report Offer
        Route::post('comments/{comment_id}/reports', [CommunityOffersCommentsController::class, 'reportComment'])
            ->name('report.add');
    });
});

//Modals
Route::group([
    'prefix' => 'modals',
    'as' => 'modals.'
], function () {

    //get modals
    Route::get('/get', ModalController::class);
});

Route::get('/advertisements', [AdvertisementsController::class, 'getAdvertisements'])
    ->name('advertisements.get');
