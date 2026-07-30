<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Apple;

use App\Helpers\Advertisers\PackageQuotas;
use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Exception;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\AppStore\Cancel;

class SubscriptionsCanceledController extends Controller
{
    /**
     * @param Cancel $event
     * @return false|void
     */
    public function handle(Cancel $event)
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
                'ends_at' => now(),
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
