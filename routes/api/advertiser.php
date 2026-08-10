<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Advertisers\Chat\ChatController;
use App\Http\Controllers\API\Advertisers\Modals\ModalController;
use App\Http\Controllers\API\Advertisers\Account\AccountController;
use App\Http\Controllers\API\Advertisers\Language\LanguageController;
use App\Http\Controllers\API\Advertisers\Requests\RequestsController;
use App\Http\Controllers\API\Advertisers\Users\UsersReportsController;
use App\Http\Controllers\API\Advertisers\Customers\CustomersController;
use App\Http\Controllers\API\Advertisers\Users\UsersBlockingsController;
use App\Http\Controllers\API\Advertisers\Categories\CategoriesController;
use App\Http\Controllers\API\Advertisers\Locations\LocationsController;
use App\Http\Controllers\API\Advertisers\Advertisers\AdvertisersController;
use App\Http\Controllers\API\Advertisers\Notifications\NotificationsController;
use App\Http\Controllers\API\Advertisers\Advertisements\AdvertisementsController;
use App\Http\Controllers\API\Advertisers\Community\Posts\CommunityPostsController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Packages\PackagesController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Packages\PackageSubscriptionRequestController;
use App\Http\Controllers\API\Advertisers\Community\Offers\CommunityOffersController;
use App\Http\Controllers\API\Advertisers\Community\Posts\Saved\SavedPostsController;
use App\Http\Controllers\API\Advertisers\Community\Comments\CommunityCommentsController;
use App\Http\Controllers\API\Advertisers\Community\Proposals\CommunityProposalsController;
use App\Http\Controllers\API\Advertisers\Community\Posts\Subscribed\SubscribedPostsController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\SubscriptionsPurchasedController;
use App\Http\Controllers\API\Advertisers\Advertisers\HiddenAdvertisers\HiddenAdvertisersController;
use App\Http\Controllers\API\Advertisers\Community\Offers\Comments\CommunityOffersCommentsController;

/*
|--------------------------------------------------------------------------
| Advertiser API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Base
Route::get('/', function () {
    return response('200 OK', 200);
})
    ->name('base');

/**
 * get Categories Routes
 */
Route::get('/account', [AccountController::class, 'getAccountData'])
    ->name('account.get');

//edit account data
Route::post('/account', [AccountController::class, 'updateAccount'])
    ->name('account.edit');

Route::post('/account/addPoints', [AccountController::class, 'addPoints'])
    ->name('account.addPoints');


Route::post('/account/increase', [AccountController::class, 'increase'])
    ->name('account.increase');

Route::post('/account/delete', [AccountController::class, 'delete'])
    ->name('account.delete');

Route::post('/ping', [AccountController::class, 'ping'])
    ->name('account.ping');

Route::post('/users/report', [UsersReportsController::class, 'reportUser'])
    ->name('users.report');

Route::get('/users/blocked', [UsersBlockingsController::class, 'getBlockedUsers'])
    ->name('users.blocked');


/**
 * Language change
 */
Route::post('/language/change', LanguageController::class)->name('language.change');

/**
 * Notifications
 */
Route::get('/notifications', [NotificationsController::class, 'getNotifications'])
    ->name('notifications.index');

Route::get('/notifications/{id}', [NotificationsController::class, 'getNotificationById'])
    ->name('notifications.show');

Route::post('/notifications/send', [NotificationsController::class, 'send'])
    ->name('notifications.send');

Route::post('/notifications/read', [NotificationsController::class, 'makeAllRead'])
    ->name('notifications.read');

Route::delete('/notifications/{id}', [NotificationsController::class, 'deleteNotificationById'])
    ->name('notifications.delete');

/**
 * get Categories Routes
 */
Route::get('/categories', [CategoriesController::class, 'getCategories'])
    ->name('categories.get');

Route::get('/categories/interested', [CategoriesController::class, 'getUserCategories'])
    ->name('categories.interested');

Route::get('/categories/{id}', [CategoriesController::class, 'getCategoryById'])
    ->name('category.get');

Route::post('/categories/interested', [CategoriesController::class, 'addAdvertiserCategories'])
    ->name('categories.add');

Route::delete('/categories/interested', [CategoriesController::class, 'deleteAdvertiserCategories'])
    ->name('categories.delete');


/**
 * Location interests
 */
Route::get('/locations/interested', [LocationsController::class, 'getUserLocations'])
    ->name('locations.interested');

Route::post('/locations/interested', [LocationsController::class, 'addUserLocations'])
    ->name('locations.add');

Route::delete('/locations/interested', [LocationsController::class, 'deleteUserLocations'])
    ->name('locations.delete');

/**
 * get Advertisers routes
 */
Route::get('/customers/{id}', [CustomersController::class, 'getCustomerById'])
    ->name('customer.get');
/**
 * get Advertisers routes
 */
//get all advertisers
Route::group([
    'prefix' => 'advertisers',
    'as' => 'advertisers.',
], function () {
    Route::get('/', [AdvertisersController::class, 'getAdvertisers'])
        ->name('advertisers.get');

    //search advertisers
    Route::post('/search', [AdvertisersController::class, 'search'])
        ->name('advertisers.search');

    //get elite advertisers
    Route::get('/elite', [AdvertisersController::class, 'getEliteAdvertisers'])
        ->name('advertisers.elite.get');

    //rate advertiser
    Route::post('{id}/rate', [AdvertisersController::class, 'rateAdvertisers'])
        ->name('advertiser.rate');

    //hide - un hide posts
    Route::post('{id}/hide', [HiddenAdvertisersController::class, 'toggleHideAdvertisers'])
        ->name('advertisers.toggle.hide');

    //get hidden posts
    Route::get('/hidden', [HiddenAdvertisersController::class, 'getHiddenAdvertisers'])
        ->name('advertisers.hidden.get');

    //get advertiser raters by advertiser id
    Route::get('/{id}/raters', [AdvertisersController::class, 'getRatersByAdvertiserId'])
        ->name('advertiser.raters');

    //get current user raters
    Route::get('/raters', [AdvertisersController::class, 'userRaters'])
        ->name('user.raters');

    //get users rated
    Route::get('/rated', [AdvertisersController::class, 'usersRated'])
        ->name('user.rated');

    //get advertiser by id
    Route::get('/{id}', [AdvertisersController::class, 'getAdvertiserById'])
        ->name('advertiser.get');
});

/**
 * Community Routes
 */
Route::group([
    'prefix' => 'community',
    'as' => 'community.',
], function () {
    //posts
    Route::group([
        'prefix' => 'posts',
        'as' => 'posts.'
    ], function () {

        //add Post
        Route::post('/', [CommunityPostsController::class, 'addPost'])
            ->name('posts.user.add');


        //get all posts
        Route::get('/', [CommunityPostsController::class, 'getAllPosts'])
            ->name('posts.get');

        //search posts
        Route::post('/search', [CommunityPostsController::class, 'search'])
            ->name('posts.search.get');

        //delete post
        Route::delete('/{id}', [CommunityPostsController::class, 'deletePost'])
            ->where('id', '[0-9]+')
            ->name('post.delete');

        //edit Post
        Route::post('/{id}', [CommunityPostsController::class, 'editPost'])
            ->where('id', '[0-9]+')
            ->name('post.edit');


        //get posts by user id
        Route::get('/advertisers/{user_id}', [CommunityPostsController::class, 'getPostsByUserId'])
            ->name('posts.user.id.get');

        //get user posts
        Route::get('/user', [CommunityPostsController::class, 'getUserPosts'])
            ->name('posts.user.get');

        //get posts interacted by customer
        Route::get('/interacted/customer/{user_id}', [CommunityPostsController::class, 'getPostsInteractedByCustomer'])
            ->name('posts.customer.interacted');

        //like post
        Route::post('/{id}/like', [CommunityPostsController::class, 'toggleLikePost'])
            ->name('posts.toggle.like');

        //save - un save posts
        Route::post('{id}/save', [SavedPostsController::class, 'toggleSavedPosts'])
            ->name('posts.toggle.save');

        //get saved posts
        Route::get('/saved', [SavedPostsController::class, 'getSavedPosts'])
            ->name('posts.saved.get');

        //subscribe - un subscribe posts
        Route::post('{id}/subscribe', [SubscribedPostsController::class, 'toggleSubscribedPosts'])
            ->name('posts.toggle.subscribe');

        //get saved posts
        Route::get('/subscribed', [SubscribedPostsController::class, 'getSubscribedPosts'])
            ->name('posts.subscribed.get');

        //report Post
        Route::post('/reports', [CommunityPostsController::class, 'reportPost'])
            ->name('posts.report.add');

        //get reported posts
        Route::get('/reports/get', [CommunityPostsController::class, 'getReportedPosts'])
            ->name('posts.reports.get');

        //edit post report
        Route::post('/reports/{id}', [CommunityPostsController::class, 'editPostReport'])
            ->name('posts.report.edit');

        //get post by id
        Route::get('/{id}', [CommunityPostsController::class, 'getPostById'])
            ->name('post.get');

        //get comments
        Route::get('{id}/comments', [CommunityCommentsController::class, 'getPostComments'])
            ->name('post.comments.get');

        //add comment
        Route::post('{id}/comments', [CommunityCommentsController::class, 'addPostComment'])
            ->name('post.comments.add');

        //comments
        Route::group([
            'prefix' => 'comments',
            'as' => 'comments.'
        ], function () {
            //edit comment
            Route::post('/{comment_id}', [CommunityCommentsController::class, 'editPostComment'])
                ->name('post.comments.edit');

            //delete comment
            Route::delete('/{comment_id}', [CommunityCommentsController::class, 'deletePostComment'])
                ->name('post.comments.delete');

            //toggle like comment
            Route::post('{id}/like', [CommunityCommentsController::class, 'toggleLikeComment'])
                ->name('toggle.like');

            //report comment
            Route::post('{comment_id}/reports', [CommunityCommentsController::class, 'reportComment'])
                ->name('comments.report.add');

            //edit comment report
            Route::post('/reports/{report_id}', [CommunityCommentsController::class, 'editCommentReport'])
                ->name('comments.report.edit');

            //get reported comments
            Route::get('/reports', [CommunityCommentsController::class, 'getReportedComments'])
                ->name('comments.reports.get');
        });
    });

    //proposals
    Route::group([
        'prefix' => 'proposals',
        'as' => 'proposals.'
    ], function () {
        Route::get('/', [CommunityProposalsController::class, 'getProposals'])
            ->name('proposals.received.get');

        Route::post('/', [CommunityProposalsController::class, 'addProposal'])
            ->name('proposal.post');

        Route::post('/{id}', [CommunityProposalsController::class, 'editProposal'])
            ->name('proposal.edit');

        Route::delete('/{id}', [CommunityProposalsController::class, 'deleteProposal'])
            ->name('proposal.delete');

        Route::post('/{id}/answer', [CommunityProposalsController::class, 'addAnswer'])
            ->name('proposal.answer');

        Route::post('/answer/{id}', [CommunityProposalsController::class, 'editAnswer'])
            ->name('proposal.edit');

        //report proposal
        Route::post('{id}/reports', [CommunityProposalsController::class, 'reportProposal'])
            ->name('proposals.report.add');

        //get reported proposals
        Route::get('/reports', [CommunityProposalsController::class, 'getReportedProposals'])
            ->name('proposals.reports.get');

        //edit proposal report
        Route::post('/reports/{id}', [CommunityProposalsController::class, 'editProposalReport'])
            ->name('proposals.report.edit');

        Route::get('/{id}', [CommunityProposalsController::class, 'getProposal'])
            ->name('proposal.get');
    });

    //Offers
    Route::group([
        'prefix' => 'offers',
        'as' => 'offers.'
    ], function () {

        //get all offers
        Route::get('/', [CommunityOffersController::class, 'getAllOffers'])
            ->name('get');

        //add offer
        Route::post('/', [CommunityOffersController::class, 'addOffer'])
            ->name('user.add');

        //search offers
        Route::post('/search', [CommunityOffersController::class, 'search'])
            ->name('search.get');


        //get offers by username
        Route::get('/username/{username}', [CommunityOffersController::class, 'getOffersByUsername'])
            ->name('username.get');

        //get offers by username
        Route::get('/user', [CommunityOffersController::class, 'getUserOffers'])
            ->name('user.get');

        //delete post
        Route::delete('/{id}', [CommunityOffersController::class, 'deleteOffer'])
            ->where('id', '[0-9]+')
            ->name('delete');

        //edit offer
        Route::post('/{id}', [CommunityOffersController::class, 'editOffer'])
            ->where('id', '[0-9]+')
            ->name('offer.edit');

        //get offers by username
        Route::post('{id}/like', [CommunityOffersController::class, 'toggleLikeOffer'])
            ->name('toggle.like');

        //report offer
        Route::post('{id}/reports', [CommunityOffersController::class, 'reportOffer'])
            ->name('report.add');

        //get reported offers
        Route::get('/reports', [CommunityOffersController::class, 'getReportedOffers'])
            ->name('reports.get');

        //edit offer report
        Route::post('/reports/{id}', [CommunityOffersController::class, 'editOfferReport'])
            ->name('report.edit');

        //rate offer
        Route::post('{id}/rate', [CommunityOffersController::class, 'rateOffers'])
            ->name('rate.add');

        //get offer by id
        Route::get('/{id}', [CommunityOffersController::class, 'getOfferById'])
            ->name('offer.get');

        Route::get('{id}/comments', [CommunityOffersCommentsController::class, 'getOfferComments'])
            ->name('comments.get');

        Route::post('{id}/comments', [CommunityOffersCommentsController::class, 'addOfferComment'])
            ->name('comments.add');

        //comments
        Route::group([
            'prefix' => 'comments',
            'as' => 'comments.'
        ], function () {
            Route::post('/{comment_id}', [CommunityOffersCommentsController::class, 'editOfferComment'])
                ->name('edit');

            Route::delete('{comment_id}', [CommunityOffersCommentsController::class, 'deleteOfferComment'])
                ->name('delete');

            //report Offer
            Route::post('{comment_id}/reports', [CommunityOffersCommentsController::class, 'reportComment'])
                ->name('report.add');

            //toggle like comment
            Route::post('{comment_id}/like', [CommunityOffersCommentsController::class, 'toggleLikeComment'])
                ->name('toggle.like');

            //edit post report
            Route::post('/reports/{comment_id}', [CommunityOffersCommentsController::class, 'editCommentReport'])
                ->name('report.edit');

            //get reported offers
            Route::get('/reports', [CommunityOffersCommentsController::class, 'getReportedComments'])
                ->name('reports.get');
        });
    });
});

//requests
Route::group([
    'prefix' => 'requests',
    'as' => 'requests.'
], function () {
    Route::post('/username/change', [RequestsController::class, 'changeUsername'])
        ->name('username.change');

    Route::get('/username/change/{id?}', [RequestsController::class, 'getUsernameChangeRequests'])
        ->name('username.change.all');
});

//chats
Route::group([
    'prefix' => 'chats',
    'as' => 'chats.',
], function () {
    Route::get('/{token?}', [ChatController::class, 'getChats'])
        ->name('chats.get');

    Route::post('/add', [ChatController::class, 'createChat'])
        ->name('chats.add');

    Route::get('/{token}/messages', [ChatController::class, 'getChatMessages'])
        ->name('chats.messages.get');

    Route::post('/{token}/messages/send', [ChatController::class, 'sendMessage'])
        ->name('chats.messages.send');

    Route::delete('/messages/{id}', [ChatController::class, 'deleteMessage'])
        ->name('chats.messages.delete');
});

/**
 * subscriptions route
 */
Route::group([
    'prefix' => 'subscriptions',
    'as' => 'subscriptions.',
], function () {

    //packages route
    Route::group([
        'prefix' => 'packages',
        'as' => 'packages.',
    ], function () {
        Route::get('/', [PackagesController::class, 'getPackages']);
        Route::get('/user', [PackagesController::class, 'getUserPackage']);
        Route::get('/payment-info', [PackageSubscriptionRequestController::class, 'paymentInfo']);
        Route::post('/requests', [PackageSubscriptionRequestController::class, 'store']);
        Route::post('/validate', [SubscriptionsPurchasedController::class, 'addPurchase']);
        Route::get('/{id}', [PackagesController::class, 'getPackageById']);
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
