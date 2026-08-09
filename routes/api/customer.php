<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Customers\Chat\ChatController;
use App\Http\Controllers\API\Customers\Modals\ModalController;
use App\Http\Controllers\API\Customers\Account\AccountController;
use App\Http\Controllers\API\Customers\Requests\RequestsController;
use App\Http\Controllers\API\Customers\Users\UsersReportsController;
use App\Http\Controllers\API\Customers\Customers\CustomersController;
use App\Http\Controllers\API\Customers\Users\UsersBlockingsController;
use App\Http\Controllers\API\Customers\Categories\CategoriesController;
use App\Http\Controllers\API\Customers\Interests\InterestsController;
use App\Http\Controllers\API\Customers\Locations\LocationsController;
use App\Http\Controllers\API\Customers\Advertisers\AdvertisersController;
use App\Http\Controllers\API\Customers\Notifications\NotificationsController;
use App\Http\Controllers\API\Customers\Advertisements\AdvertisementsController;
use App\Http\Controllers\API\Customers\Community\Posts\CommunityPostsController;
use App\Http\Controllers\API\Customers\Community\Offers\CommunityOffersController;
use App\Http\Controllers\API\Customers\Community\Posts\Saved\SavedPostsController;
use App\Http\Controllers\API\Customers\Community\Comments\CommunityCommentsController;
use App\Http\Controllers\API\Customers\Community\Proposals\CommunityProposalsController;
use App\Http\Controllers\API\Customers\Community\Posts\Subscribed\SubscribedPostsController;
use App\Http\Controllers\API\Customers\Advertisers\HiddenAdvertisers\HiddenAdvertisersController;
use App\Http\Controllers\API\Customers\Community\Offers\Comments\CommunityOffersCommentsController;
use App\Http\Controllers\API\Customers\Language\LanguageController;

/*
|--------------------------------------------------------------------------
| Customers API Routes
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

Route::post('/account/delete', [AccountController::class, 'delete'])
    ->name('account.delete');


//ping account status whether it's online or not
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
 * get Interests Routes
 */
Route::get('/interests', [InterestsController::class, 'getInterests'])
    ->name('interests.get');

Route::get('/interests/interested', [InterestsController::class, 'getUserInterests'])
    ->name('interests.interested');

Route::get('/interests/{id}', [InterestsController::class, 'getInterestById'])
    ->name('interest.get');

Route::post('/interests/interested', [InterestsController::class, 'addAdvertiserInterests'])
    ->name('interests.add');

Route::delete('/interests/interested', [InterestsController::class, 'deleteAdvertiserInterests'])
    ->name('interests.delete');

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

    //get all advertisers
    Route::get('/', [AdvertisersController::class, 'getAdvertisers'])
        ->name('advertisers.get');

    //search advertisers
    Route::post('/search', [AdvertisersController::class, 'search'])
        ->name('advertisers.search');

    //get elite advertisers
    Route::get('/elite', [AdvertisersController::class, 'getEliteAdvertisers'])
        ->name('advertisers.elite.get');

    //rate advertisers
    Route::post('{id}/rate', [AdvertisersController::class, 'rateAdvertisers'])
        ->where('id', '[0-9]+')
        ->name('advertiser.rate');

    //hide - un hide posts
    Route::post('{id}/hide', [HiddenAdvertisersController::class, 'toggleHideAdvertisers'])
        ->where('id', '[0-9]+')
        ->name('advertisers.toggle.hide');

    //get hidden posts
    Route::get('/hidden', [HiddenAdvertisersController::class, 'getHiddenAdvertisers'])
        ->name('advertisers.hidden.get');

    //get advertiser raters by advertiser id
    Route::get('/{id}/raters', [AdvertisersController::class, 'getRatersByAdvertiserId'])
        ->where('id', '[0-9]+')
        ->name('advertiser.raters');

    //get users rated
    Route::get('/rated', [AdvertisersController::class, 'usersRated'])
        ->name('user.rated');

    //get advertiser by id
    Route::get('/{id}', [AdvertisersController::class, 'getAdvertiserById'])
        ->where('id', '[0-9]+')
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

        //get all posts
        Route::get('/', [CommunityPostsController::class, 'getAllPosts'])
            ->name('posts.get');

        //search posts
        Route::post('/search', [CommunityPostsController::class, 'search'])
            ->name('posts.search.get');

        //get posts by user id
        Route::get('/advertisers/{user_id}', [CommunityPostsController::class, 'getPostsByUserId'])
            ->where('user_id', '[0-9]+')
            ->name('posts.user.id.get');

        //get posts interacted by customer
        Route::get('/interacted/customer/{user_id}', [CommunityPostsController::class, 'getPostsInteractedByCustomer'])
            ->name('posts.customer.interacted');

        //like post
        Route::post('{id}/like', [CommunityPostsController::class, 'toggleLikePost'])
            ->where('id', '[0-9]+')
            ->name('posts.toggle.like');

        //save - un save posts
        Route::post('{id}/save', [SavedPostsController::class, 'toggleSavedPosts'])
            ->where('id', '[0-9]+')
            ->name('posts.toggle.save');

        //get saved posts
        Route::get('/saved', [SavedPostsController::class, 'getSavedPosts'])
            ->name('posts.saved.get');

        //subscribe - un subscribe posts
        Route::post('{id}/subscribe', [SubscribedPostsController::class, 'toggleSubscribedPosts'])
            ->where('id', '[0-9]+')
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
            ->where('id', '[0-9]+')
            ->name('posts.report.edit');

        //get post by id
        Route::get('/{id}', [CommunityPostsController::class, 'getPostById'])
            ->where('id', '[0-9]+')
            ->name('post.get');

        //get comments
        Route::get('{id}/comments', [CommunityCommentsController::class, 'getPostComments'])
            ->where('id', '[0-9]+')
            ->name('post.comments.get');

        //add comment
        Route::post('{id}/comments', [CommunityCommentsController::class, 'addPostComment'])
            ->where('id', '[0-9]+')
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
        Route::get('/', [CommunityProposalsController::class, 'getUserProposals'])
            ->name('proposals.get');

        Route::post('/', [CommunityProposalsController::class, 'addProposal'])
            ->name('proposal.post');

        Route::post('/{id}', [CommunityProposalsController::class, 'editProposal'])
            ->name('proposal.edit');

        Route::delete('/{id}', [CommunityProposalsController::class, 'deleteProposal'])
            ->name('proposal.delete');

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

        //search offers
        Route::post('/search', [CommunityOffersController::class, 'search'])
            ->name('search.get');

        //get offers by username
        Route::get('/username/{username}', [CommunityOffersController::class, 'getOffersByUsername'])
            ->name('username.get');

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

            Route::delete('/{comment_id}', [CommunityOffersCommentsController::class, 'deleteOfferComment'])
                ->name('delete');

            //toggle like comment
            Route::post('{comment_id}/like', [CommunityOffersCommentsController::class, 'toggleLikeComment'])
                ->name('toggle.like');

            //report Offer
            Route::post('{id}/reports', [CommunityOffersCommentsController::class, 'reportComment'])
                ->name('report.add');

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
