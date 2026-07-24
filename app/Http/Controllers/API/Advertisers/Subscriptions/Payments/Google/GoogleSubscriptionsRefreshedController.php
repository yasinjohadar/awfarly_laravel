<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google;

use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\AppStore\DidRenew;
use Imdhemy\Purchases\Events\AppStore\InteractiveRenewal;

class GoogleSubscriptionsRefreshedController extends Controller
{
    /**
     * @param InteractiveRenewal $event
     * @return false|void
     */
    public function handle(InteractiveRenewal $event)
    {
        // The following data can be retrieved from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $expirationTime = $subscription->getExpiryTime();

        $package = AdvertiserPackages::where('unique_identifier', $uniqueIdentifier)
            ->first();

        if (!$package) {
            return false;
        }
        DB::beginTransaction();
        try {
            AdvertiserPackages::where('advertiser_id', '!=', $package->advertiser_id)
                ->where('unique_identifier', '!=', $uniqueIdentifier)
                ->where('is_current', true)
                ->where('is_active', true)
                ->update([
                    'ends_at' => now(),
                    'is_ended' => true,
                    'is_current' => false,
                    'is_active' => false,
                ]);

            $package->update([
                'ends_at' => $expirationTime,
                'purchase_count' => DB::raw('purchase_count+1'),
                'is_ended' => false,
                'is_current' => true,
                'is_active' => true,
            ]);

            $package->advertiser()
                ->update([
                    'is_elite' => true,
                ]);

        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }
}
