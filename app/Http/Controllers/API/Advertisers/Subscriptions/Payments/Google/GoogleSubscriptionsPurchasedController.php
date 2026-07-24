<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google;

use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Subscriptions\Payments\Google\GooglePurchases;
use Exception;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPurchased;

class GoogleSubscriptionsPurchasedController extends Controller
{
    /**
     * @param SubscriptionPurchased $event
     * @return void
     */
    public function handle(SubscriptionPurchased $event)
    {
        // The following data can be retrieved from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $product_id = $subscription->getItemId();
        $expiration_date = $subscription->getExpiryTime();

        $package = AdvertiserPackages::where('unique_identifier', $uniqueIdentifier)
            ->first();

        /*DB::beginTransaction();
        try {
            if (!$package) {
                GooglePurchases::query()
                    ->create([
                        'subscription' => $subscription,
                        'unique_identifier' => $uniqueIdentifier,
                        'product_id' => $product_id,
                        'expiration_date' => $expiration_date,
                    ]);
            } else {
                $package->update([
                    'ends_at' => now(),
                    'is_ended' => true,
                    'is_current' => false,
                    'is_active' => false,
                ]);
                $advertiser = $package->advertiser;
                $package_exists = $advertiser->packages()
                    ->where('is_current', true)
                    ->where('is_active', true)
                    ->where('is_ended', false)
                    ->first();

                if (!$package_exists) {
                    $advertiser->update([
                        'is_elite' => true,
                    ]);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();*/
    }
}
