<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Google;

use App\Helpers\Advertisers\PackageQuotas;
use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Exception;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\AppStore\InteractiveRenewal;

class GoogleSubscriptionsRenewedManualController extends Controller
{
    /**
     * @param InteractiveRenewal $event
     * @return false|void
     */
    public function handle(InteractiveRenewal $event)
    {
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $expirationTime = $subscription->getExpiryTime();

        $package = AdvertiserPackages::with('package')
            ->where('unique_identifier', $uniqueIdentifier)
            ->first();

        if (!$package) {
            return false;
        }

        DB::beginTransaction();
        try {
            AdvertiserPackages::where('advertiser_id', $package->advertiser_id)
                ->where('id', '!=', $package->id)
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

            if ($package->advertiser && $package->package) {
                PackageQuotas::applyFromPackage($package->advertiser, $package->package);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
    }
}
