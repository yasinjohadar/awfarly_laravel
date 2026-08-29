<?php

namespace App\Helpers\Advertisers;

use App\Helpers\Settings;
use App\Models\Users\Advertisers\AdvertiserUser;
use Carbon\Carbon;

class OfferLimits
{
    /**
     * Max concurrent active (non-expired) offers.
     * allowed_offers_count is the assigned concurrent ceiling (from package/admin), not remaining credits.
     */
    public static function activeLimit(AdvertiserUser $advertiser): int
    {
        $package = self::currentPackage($advertiser);
        if ($package && $package->maximum_offers !== null) {
            return (int) $package->maximum_offers;
        }

        if ($advertiser->allowed_offers_count !== null) {
            return (int) $advertiser->allowed_offers_count;
        }

        return (int) Settings::Get('max.advertiser.active.offers', 20);
    }

    /**
     * Max offers that may be created in the current calendar month.
     */
    public static function monthlyLimit(AdvertiserUser $advertiser): int
    {
        $package = self::currentPackage($advertiser);
        if ($package && $package->maximum_monthly_offers !== null) {
            return (int) $package->maximum_monthly_offers;
        }

        if ($advertiser->maximum_monthly_offers !== null) {
            return (int) $advertiser->maximum_monthly_offers;
        }

        return (int) Settings::Get('max.advertiser.monthly.offers', 30);
    }

    public static function activeCount(AdvertiserUser $advertiser): int
    {
        return $advertiser->offers()
            ->where(function ($q) {
                $q->where('expires_at', '>', now())
                    ->orWhereNull('expires_at');
            })
            ->count();
    }

    /**
     * IDs of the advertiser's offers that should be visible to customers/guests:
     * the N most recent customer-facing-active offers, where N = activeLimit().
     * "Customer-facing active" = status approved AND expires_at in the future —
     * intentionally narrower than activeCount()'s null-expiry-inclusive definition,
     * to match the WHERE clauses already used by getOffersByUsername.
     */
    public static function cappedActiveOfferIds(AdvertiserUser $advertiser): \Illuminate\Support\Collection
    {
        $limit = max(0, self::activeLimit($advertiser));

        return $advertiser->offers()
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    public static function monthlyCount(AdvertiserUser $advertiser, ?Carbon $at = null): int
    {
        $at = $at ?: now();

        return $advertiser->offers()
            ->withTrashed()
            ->whereBetween('created_at', [
                $at->copy()->startOfMonth(),
                $at->copy()->endOfMonth(),
            ])
            ->count();
    }

    /**
     * @return array{allowed: bool, reason: string|null, activeCount: int, activeLimit: int, monthlyCount: int, monthlyLimit: int}
     */
    public static function evaluate(AdvertiserUser $advertiser): array
    {
        $activeCount = self::activeCount($advertiser);
        $activeLimit = self::activeLimit($advertiser);
        $monthlyCount = self::monthlyCount($advertiser);
        $monthlyLimit = self::monthlyLimit($advertiser);

        $reason = null;
        if ($activeCount >= $activeLimit) {
            $reason = 'active';
        } elseif ($monthlyCount >= $monthlyLimit) {
            $reason = 'monthly';
        }

        return [
            'allowed' => $reason === null,
            'reason' => $reason,
            'activeCount' => $activeCount,
            'activeLimit' => $activeLimit,
            'monthlyCount' => $monthlyCount,
            'monthlyLimit' => $monthlyLimit,
        ];
    }

    protected static function currentPackage(AdvertiserUser $advertiser)
    {
        $subscription = $advertiser->packages()
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->first();

        return $subscription ? $subscription->package : null;
    }
}
