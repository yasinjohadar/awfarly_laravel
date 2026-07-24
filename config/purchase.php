<?php

use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Apple\SubscriptionsCanceledController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Apple\SubscriptionsFailedToRenewController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Apple\SubscriptionsRenewedManualController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google\GoogleSubscriptionsCanceledController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google\GoogleSubscriptionsExpiredController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google\GoogleSubscriptionsPurchasedController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google\GoogleSubscriptionsRefreshedController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google\GoogleSubscriptionsRenewedAutomaticController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\SubscriptionsPurchasedController;
use App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Apple\SubscriptionsRenewedAutomaticController;
use Imdhemy\Purchases\Events\AppStore\Cancel;
use Imdhemy\Purchases\Events\AppStore\DidChangeRenewalPref;
use Imdhemy\Purchases\Events\AppStore\DidChangeRenewalStatus;
use Imdhemy\Purchases\Events\AppStore\DidFailToRenew;
use Imdhemy\Purchases\Events\AppStore\DidRecover;
use Imdhemy\Purchases\Events\AppStore\DidRenew;
use Imdhemy\Purchases\Events\AppStore\InitialBuy;
use Imdhemy\Purchases\Events\AppStore\InteractiveRenewal;
use Imdhemy\Purchases\Events\AppStore\PriceIncreaseConsent;
use Imdhemy\Purchases\Events\AppStore\Refund;
use Imdhemy\Purchases\Events\AppStore\Revoke;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionCanceled;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionDeferred;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionExpired;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionInGracePeriod;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionOnHold;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPaused;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPauseScheduleChanged;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPriceChangeConfirmed;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPurchased;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRecovered;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRestarted;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRevoked;

return [
    'routing' => [
        'middleware' => 'api',
        'prefix' => 'api/v1/'
    ],

    'google_play_package_name' => env('GOOGLE_PLAY_PACKAGE_NAME', 'com.example.name'),

    'appstore_password' => env('APPSTORE_PASSWORD', ''),

    'eventListeners' => [
        /**
         * --------------------------------------------------------
         * Google Play Events
         * --------------------------------------------------------
         */
        SubscriptionPurchased::class => [GoogleSubscriptionsPurchasedController::class],
        SubscriptionRenewed::class => [GoogleSubscriptionsRenewedAutomaticController::class],
        SubscriptionInGracePeriod::class => [],
        SubscriptionExpired::class => [GoogleSubscriptionsExpiredController::class],
        SubscriptionCanceled::class => [GoogleSubscriptionsCanceledController::class],
        SubscriptionPaused::class => [],
        SubscriptionRestarted::class => [GoogleSubscriptionsRefreshedController::class],
        SubscriptionDeferred::class => [],
        SubscriptionRevoked::class => [],
        SubscriptionOnHold::class => [],
        SubscriptionRecovered::class => [],
        SubscriptionPauseScheduleChanged::class => [],
        SubscriptionPriceChangeConfirmed::class => [],

        /**
         * --------------------------------------------------------
         * App store Events
         * --------------------------------------------------------
         */
        Cancel::class => [SubscriptionsCanceledController::class],
        DidChangeRenewalPref::class => [],
        DidChangeRenewalStatus::class => [],
        DidFailToRenew::class => [SubscriptionsFailedToRenewController::class],
        DidRecover::class => [],
        DidRenew::class => [SubscriptionsRenewedAutomaticController::class],
        InitialBuy::class => [SubscriptionsPurchasedController::class],
        InteractiveRenewal::class => [SubscriptionsRenewedManualController::class],
        PriceIncreaseConsent::class => [],
        Refund::class => [],
        Revoke::class => [],
    ],
];
