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
            $limit = (int) $package->maximum_offers;
        } elseif ($advertiser->allowed_offers_count !== null) {
            $limit = (int) $advertiser->allowed_offers_count;
        } else {
            $limit = (int) Settings::Get('max.advertiser.active.offers', 20);
        }

        return self::capBySetting($limit, 'max.advertiser.active.offers', 20);
    }

    /**
     * Max offers that may be created in the current calendar month.
     */
    public static function monthlyLimit(AdvertiserUser $advertiser): ?int
    {
        if (!self::isEnabled('max.advertiser.monthly.offers', 30)) {
            return null;
        }

        $package = self::currentPackage($advertiser);
        if ($package && $package->maximum_monthly_offers !== null) {
            $limit = (int) $package->maximum_monthly_offers;
        } elseif ($advertiser->maximum_monthly_offers !== null) {
            $limit = (int) $advertiser->maximum_monthly_offers;
        } else {
            $limit = (int) Settings::Get('max.advertiser.monthly.offers', 30);
        }

        return self::capBySetting($limit, 'max.advertiser.monthly.offers', 30);
    }

    /**
     * Only the MONTHLY limit can be switched off: a 0 there means the admin does
     * not work with a monthly quota at all, so nothing is enforced and the app is
     * told there is no such limit (a null in the account statistics) and drops it
     * from the interface rather than showing a confusing "0 of 0". The active
     * offers limit always applies - a 0 there only means "no admin ceiling", and
     * the package quota governs.
     */
    public static function isEnabled(string $key, int $default): bool
    {
        return (int) Settings::Get($key, $default) > 0;
    }

    /**
     * The admin setting is a hard ceiling, not just a fallback: a package may
     * sell a bigger quota, but no advertiser may publish past what the admin
     * configured. A setting of 0 (or less) turns the ceiling off, matching how
     * the other numeric settings read a 0.
     */
    public static function settingCap(string $key, int $default): ?int
    {
        $cap = (int) Settings::Get($key, $default);

        return $cap > 0 ? $cap : null;
    }

    protected static function capBySetting(int $limit, string $key, int $default): int
    {
        $cap = self::settingCap($key, $default);

        return $cap === null ? $limit : min($limit, $cap);
    }

    /**
     * When every active slot is taken, the earliest of them frees itself on
     * this date - the advertiser is told it instead of just being refused.
     */
    public static function nextExpiryAt(AdvertiserUser $advertiser): ?Carbon
    {
        $offer = $advertiser->offers()
            ->where('status', '!=', 'unapproved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->first();

        return $offer ? Carbon::make($offer->expires_at) : null;
    }

    /**
     * Offers currently occupying one of the advertiser's active slots.
     *
     * A null expires_at counts, because an offer awaiting approval still holds
     * its slot. A REJECTED offer does not: rejection leaves expires_at untouched,
     * so without this it would keep occupying a slot forever and block the
     * advertiser below the ceiling the admin actually set.
     */
    public static function activeCount(AdvertiserUser $advertiser): int
    {
        return $advertiser->offers()
            ->where('status', '!=', 'unapproved')
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
     * A null monthlyLimit means the monthly quota is switched off in the settings
     * and must not be enforced or displayed. activeLimit always applies.
     *
     * @return array{allowed: bool, reason: string|null, activeCount: int, activeLimit: int, monthlyCount: int, monthlyLimit: int|null}
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
        } elseif ($monthlyLimit !== null && $monthlyCount >= $monthlyLimit) {
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
