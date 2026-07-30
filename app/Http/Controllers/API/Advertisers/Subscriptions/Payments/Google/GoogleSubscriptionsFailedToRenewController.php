<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google;

use App\Helpers\Advertisers\PackageQuotas;
use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Exception;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\AppStore\DidFailToRenew;

class GoogleSubscriptionsFailedToRenewController extends Controller
{
    /**
     * @param DidFailToRenew $event
     * @return false|void
     */
    public function handle(DidFailToRenew $event)
    {
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();

        $package = AdvertiserPackages::where('unique_identifier', $uniqueIdentifier)
            ->first();

        if (!$package) {
            return false;
        }

        DB::beginTransaction();
        try {
            $package->update([
                'is_ended' => true,
                'is_current' => false,
                'is_active' => false,
            ]);

            if ($package->advertiser) {
                PackageQuotas::afterSubscriptionEnded($package->advertiser);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
    }
}
