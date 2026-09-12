<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments;

use App\Http\Controllers\Controller;
use App\Helpers\Advertisers\PackageQuotas;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Subscriptions\Payments\Google\GooglePurchases;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\AppStore\InitialBuy;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPurchased;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed;
use Imdhemy\Purchases\Facades\Product;
use Imdhemy\Purchases\Facades\Subscription;
use Log;

class SubscriptionsPurchasedController extends Controller
{
    /**
     * @param InitialBuy $event
     */
    public function handle(InitialBuy $event)
    {
        // The following data can be retrieved from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $provider = $subscription->getProvider();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $expirationTime = $subscription->getExpiryTime();
        $item_id = $subscription->getItemId();

        Log::debug('apple purchases', [
            'event' => $event,
            'subscription' => $subscription,
            'provider' => $provider,
            'uniqueIdentifier' => $uniqueIdentifier,
            'expirationTime' => $expirationTime,
            'item_id' => $item_id,
        ]);

        /*$name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';
        DB::beginTransaction();
        try {

        } catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();*/
    }
}
