<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments\Apple;

use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Exception;
use Illuminate\Http\Request;
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
        // The following data can be retrieved from the event
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
        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }
}
