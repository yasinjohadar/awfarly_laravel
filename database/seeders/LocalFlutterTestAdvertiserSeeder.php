<?php

namespace Database\Seeders;

use App\Models\Categories\Category;
use App\Models\Countries\Cities\City;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a local advertiser login for the Flutter app, so the advertiser-only
 * flows (add post/offer, own categories vs interests) can be tested without
 * OTP.
 *
 * Country code is fixed to +963 in the app. Enter in the app as:
 *   Syria (+963) then 900000001
 * Password: password
 */
class LocalFlutterTestAdvertiserSeeder extends Seeder
{
    public function run()
    {
        $mobile = '+963900000001';

        $city = City::with('governorate')->orderBy('id')->first();
        //a business type that owns categories (not "Shopper")
        $businessType = AdvertiserBusinessType::where('has_categories', true)
            ->orderBy('id')
            ->first();

        if (!$city || !$businessType) {
            $this->command->warn('LocalFlutterTestAdvertiserSeeder skipped: geography or business types not seeded.');
            return;
        }

        $advertiser = AdvertiserUser::withTrashed()
            ->where('mobile', $mobile)
            ->orWhere('mobile', '963900000001')
            ->orWhere('username', 'flutter_test_adv')
            ->first();

        $payload = [
            'name' => 'معلن تجريبي',
            'username' => 'flutter_test_adv',
            'email' => 'flutter.test.adv@awfarly.test',
            'mobile' => $mobile,
            'bio' => 'حساب معلن تجريبي لاختبار التطبيق محلياً.',
            'business_type' => $businessType->id,
            'country_code' => 'SY',
            'governorate_id' => optional($city)->governorate_id,
            'city_id' => optional($city)->id,
            'language_code' => 'ar',
            'notify_language' => 'ar',
            'gender' => 'male',
            'email_verified_at' => now(),
            'mobile_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'allowed_posts_count' => 20,
            'allowed_offers_count' => 20,
            'maximum_monthly_offers' => 30,
            'is_follow_allowed' => true,
            'is_accepted_send_notifications' => true,
            'chats_privacy' => 'public',
            'profile_privacy' => 'public',
            'deleted_at' => null,
        ];

        if ($advertiser) {
            $advertiser->fill($payload)->save();
            $this->command->info("Updated advertiser #{$advertiser->id} mobile={$mobile}");
        } else {
            $advertiser = AdvertiserUser::create($payload);
            $this->command->info("Created advertiser #{$advertiser->id} mobile={$mobile}");
        }

        //give it a distinct own category and interest so both drawer screens
        //have something to show, and posting has a default category
        $ownCategory = Category::orderBy('id')->first();
        $interest = Category::orderBy('id', 'desc')->first();

        if ($ownCategory) {
            $advertiser->categories()->firstOrCreate(['category_id' => $ownCategory->id]);
        }
        if ($interest) {
            $advertiser->interests()->firstOrCreate(['category_id' => $interest->id]);
        }

        $this->command->info('Login: +963 900000001 / password');
    }
}
