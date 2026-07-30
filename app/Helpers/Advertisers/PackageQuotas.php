<?php

namespace App\Helpers\Advertisers;

use App\Helpers\Settings;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;

class PackageQuotas
{
    /**
     * Apply paid package limits to an advertiser.
     * - allowed_posts_count: remaining post credits for the period
     * - allowed_offers_count: max concurrent active offers
     * - maximum_monthly_offers: max offers creatable in a calendar month
     */
    public static function applyFromPackage(AdvertiserUser $advertiser, Package $package): void
    {
        $advertiser->update([
            'is_elite' => true,
            'allowed_posts_count' => (int) ($package->maximum_posts ?? Settings::Get('user.allowed.posts', 10)),
            'allowed_offers_count' => (int) ($package->maximum_offers ?? Settings::Get('max.advertiser.active.offers', 20)),
            'maximum_monthly_offers' => (int) ($package->maximum_monthly_offers ?? Settings::Get('max.advertiser.monthly.offers', 30)),
        ]);
    }

    /**
     * Reset advertiser to free-tier limits (no active paid package).
     */
    public static function applyFreeTier(AdvertiserUser $advertiser): void
    {
        $advertiser->update([
            'is_elite' => false,
            'allowed_posts_count' => (int) Settings::Get('user.allowed.posts', 10),
            'allowed_offers_count' => (int) Settings::Get('max.advertiser.active.offers', 20),
            'maximum_monthly_offers' => (int) Settings::Get('max.advertiser.monthly.offers', 30),
        ]);
    }

    /**
     * Sync quotas from the advertiser's currently active subscription, or free tier.
     */
    public static function syncFromActiveSubscription(AdvertiserUser $advertiser): void
    {
        $subscription = $advertiser->packages()
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->whereHas('package')
            ->with('package')
            ->first();

        if (!$subscription || !$subscription->package) {
            self::applyFreeTier($advertiser);
            return;
        }

        self::applyFromPackage($advertiser, $subscription->package);
    }

    /**
     * Calculate subscription end date from package duration settings.
     */
    public static function endsAt(Package $package)
    {
        if ($package->subscription_type === 'daily') {
            return now()->addDays($package->duration);
        }

        if ($package->subscription_type === 'weekly') {
            return now()->addWeeks($package->duration);
        }

        if (in_array($package->subscription_type, ['monthly', 'two_months', 'three_months', 'six_months'], true)) {
            return now()->addMonths($package->duration);
        }

        return now()->addYears($package->duration ?: 1);
    }

    /**
     * End current subscriptions (if any), assign a package, and apply quotas immediately.
     * Pass null to remove paid package and restore free tier.
     */
    public static function assignPackage(AdvertiserUser $advertiser, ?Package $package): void
    {
        $advertiser->packages()
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->update([
                'is_active' => false,
                'is_ended' => true,
                'is_current' => false,
                'ends_at' => now(),
            ]);

        if (!$package) {
            self::applyFreeTier($advertiser);
            return;
        }

        $advertiser->packages()->create([
            'package_id' => $package->id,
            'is_active' => true,
            'is_ended' => false,
            'is_current' => true,
            'starts_at' => now(),
            'ends_at' => self::endsAt($package),
        ]);

        self::applyFromPackage($advertiser, $package);
    }

    /**
     * After ending a subscription row: free tier if none left, else sync remaining package.
     */
    public static function afterSubscriptionEnded(AdvertiserUser $advertiser): void
    {
        self::syncFromActiveSubscription($advertiser);
    }
}
