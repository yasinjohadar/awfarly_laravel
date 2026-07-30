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
