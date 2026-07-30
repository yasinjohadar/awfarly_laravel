<?php

namespace Database\Seeders;

use App\Models\Countries\Cities\City;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a local login account for the Flutter app without OTP verification.
 *
 * Mobile as sent by Flutter InternationalPhoneNumberInput: +905519665883
 * Enter in the app as: Turkey (+90) then 5519665883 (without leading 0)
 * Password: password
 */
class LocalFlutterTestUserSeeder extends Seeder
{
    public function run()
    {
        $mobile = '+9635519665883';
        $city = City::with('governorate')->orderBy('id')->first();

        $customer = CustomerUser::withTrashed()->where('mobile', $mobile)
            ->orWhere('mobile', '+905519665883')
            ->orWhere('id', 52)
            ->first();

        $payload = [
            'name' => 'مستخدم تجريبي',
            'username' => 'flutter_test_sy',
            'email' => 'flutter.test.sy@awfarly.test',
            'mobile' => $mobile,
            'bio' => 'حساب تجريبي لتسجيل الدخول من تطبيق Flutter محلياً.',
            'country_code' => 'SY',
            'governorate_id' => optional($city)->governorate_id,
            'city_id' => optional($city)->id,
            'language_code' => 'ar',
            'notify_language' => 'ar',
            'email_verified_at' => now(),
            'mobile_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_follow_allowed' => true,
            'is_accepted_send_notifications' => true,
            'chats_privacy' => 'public',
            'profile_privacy' => 'public',
            'deleted_at' => null,
        ];

        if ($customer) {
            $customer->fill($payload)->save();
            $this->command->info("Updated customer #{$customer->id} mobile={$mobile}");
        } else {
            $customer = CustomerUser::create($payload);
            $this->command->info("Created customer #{$customer->id} mobile={$mobile}");
        }

        $this->command->info('Login: +963 5519665883 / password');
    }
}
