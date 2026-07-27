<?php

namespace Database\Seeders;

use App\Models\Categories\Category;
use App\Models\Countries\Cities\City;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoUsersSeeder extends Seeder
{
    /**
     * Seed ~30 advertisers and ~50 customers across Syrian governorates.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        AdvertiserCategories::truncate();
        CustomerCategories::truncate();
        AdvertiserPackages::truncate();
        AdvertiserUser::truncate();
        CustomerUser::truncate();
        Schema::enableForeignKeyConstraints();

        $cities = City::with('governorate')->orderBy('governorate_id')->orderBy('order')->get();
        $businessTypes = AdvertiserBusinessType::pluck('id')->all();
        $leafCategories = Category::whereNotNull('parent_category_id')->pluck('id')->all();
        $packages = Package::orderBy('id')->get();
        $password = Hash::make('password');
        $now = now();

        if ($cities->isEmpty()) {
            $this->command->warn('DemoUsersSeeder skipped: no cities found.');
            return;
        }

        $businessNames = [
            'سوق الشام للعطور', 'متجر النور للإلكترونيات', 'مطعم أبو كريم', 'كافيه بردى',
            'أزياء حلب ستايل', 'صيدلية الفيحاء', 'أثاث المنزل الذهبي', 'حلويات دمشق',
            'معرض السيارات السريع', 'مركز ليزر بيوتي', 'مكتبة الأمل', 'مطعم البحر المتوسط',
            'متجر الموبايل برو', 'ورشة النجارة الحديثة', 'عيادة الابتسامة', 'فندق الواحة',
            'سوبر ماركت الخير', 'أحذية فرعوني', 'مغسلة السيارات النظيفة', 'معهد اللغات الذكي',
            'مخبز الشام العتيق', 'مركز صيانة الجوالات', 'بوتيك روز', 'مطعم الفلافل الملكي',
            'متجر الأدوات المنزلية', 'صالون فرح', 'شركة النقل السريع', 'محل الهدايا الفريدة',
            'مزرعة الألبان الطازجة', 'استوديو التصوير الرقمي',
        ];

        $advertisers = [];
        foreach ($businessNames as $index => $name) {
            $city = $cities[$index % $cities->count()];
            $package = $packages[$index % $packages->count()];
            $isElite = $index < 5;

            $advertiser = AdvertiserUser::create([
                'name' => $name,
                'business_type' => $businessTypes[$index % count($businessTypes)],
                'username' => 'adv' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'email' => 'advertiser' . ($index + 1) . '@awfarly.test',
                'mobile' => '9639' . str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                'bio' => 'معلن تجريبي من ' . $city->name_ar . ' على منصة أوفرلي.',
                'country_code' => 'SY',
                'governorate_id' => $city->governorate_id,
                'city_id' => $city->id,
                'language_code' => 'ar',
                'notify_language' => 'ar',
                'contact_number' => '9639' . str_pad((string) (20000000 + $index), 8, '0', STR_PAD_LEFT),
                'whatsapp_number' => '9639' . str_pad((string) (20000000 + $index), 8, '0', STR_PAD_LEFT),
                'website_url' => 'https://awfarly.test/adv/' . ($index + 1),
                'allowed_posts_count' => $package->maximum_posts,
                'allowed_offers_count' => $package->maximum_offers,
                'maximum_monthly_offers' => $package->maximum_monthly_offers,
                'email_verified_at' => $now,
                'mobile_verified_at' => $now,
                'password' => $password,
                'rate' => round(3.5 + (($index % 15) / 10), 1),
                'status' => 'active',
                'is_elite' => $isElite,
                'is_follow_allowed' => true,
                'is_accepted_send_notifications' => true,
                'address_latitude' => 33.5 + (($index % 10) * 0.05),
                'address_longitude' => 36.3 + (($index % 10) * 0.05),
                'chats_privacy' => 'public',
                'profile_privacy' => 'public',
                'last_login_at' => $now->copy()->subHours($index),
                'last_online_at' => $now->copy()->subMinutes($index * 3),
                'is_online' => $index < 8,
                'discount_percentage' => $index % 4 === 0 ? 10 : 0,
            ]);

            $categoryIds = collect($leafCategories)->shuffle()->take(3)->values();
            foreach ($categoryIds as $categoryId) {
                AdvertiserCategories::create([
                    'advertiser_id' => $advertiser->id,
                    'category_id' => $categoryId,
                ]);
            }

            AdvertiserPackages::create([
                'unique_identifier' => 'demo-pkg-' . $advertiser->id,
                'package_id' => $package->id,
                'advertiser_id' => $advertiser->id,
                'starts_at' => $now->copy()->subDays(5),
                'ends_at' => $now->copy()->addDays($package->duration),
                'purchase_count' => 1,
                'is_ended' => false,
                'is_current' => true,
                'is_active' => true,
            ]);

            $advertisers[] = $advertiser;
        }

        $customerNames = [
            'أحمد الخطيب', 'سارة محمود', 'محمد علي', 'نور الحسن', 'خالد يوسف',
            'ليلى عمر', 'يوسف إبراهيم', 'رنا سعيد', 'عمر فارس', 'مريم داود',
            'حسن مصطفى', 'دانا كريم', 'باسل نزار', 'هبة سمير', 'فادي جورج',
            'سلمى وائل', 'طلال أنور', 'جنى رامي', 'إياد نهاد', 'ريما عدنان',
            'وسيم زياد', 'لينا شادي', 'نادر هاني', 'غادة بشار', 'زياد ماهر',
            'هناء فواز', 'كريم نبيل', 'ديمة عاصم', 'رافع جمال', 'شهد وليد',
            'أنس طارق', 'ميساء حسام', 'علاء بدر', 'نادين سامي', 'مروان قاسم',
            'بتول أمين', 'حسام نجم', 'رغد سيف', 'جمال عارف', 'سوزان هيثم',
            'نضال فادي', 'يارا مروان', 'أيمن سامر', 'هند راشد', 'مازن كمال',
            'رولا نزار', 'فؤاد باسم', 'لارا عماد', 'سامي حيدر', 'تالا زياد',
        ];

        foreach ($customerNames as $index => $name) {
            $city = $cities[$index % $cities->count()];

            $customer = CustomerUser::create([
                'name' => $name,
                'username' => 'cust' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'email' => 'customer' . ($index + 1) . '@awfarly.test',
                'mobile' => '9639' . str_pad((string) (30000000 + $index), 8, '0', STR_PAD_LEFT),
                'bio' => 'عميل تجريبي من ' . $city->name_ar . '.',
                'country_code' => 'SY',
                'governorate_id' => $city->governorate_id,
                'city_id' => $city->id,
                'language_code' => 'ar',
                'notify_language' => 'ar',
                'email_verified_at' => $now,
                'mobile_verified_at' => $now,
                'password' => $password,
                'status' => 'active',
                'is_follow_allowed' => true,
                'is_accepted_send_notifications' => true,
                'address_latitude' => 33.4 + (($index % 12) * 0.04),
                'address_longitude' => 36.2 + (($index % 12) * 0.04),
                'chats_privacy' => 'public',
                'profile_privacy' => 'public',
                'last_login_at' => $now->copy()->subHours($index % 20),
                'last_online_at' => $now->copy()->subMinutes($index * 2),
                'is_online' => $index < 12,
                'gender' => $index % 2 === 0 ? 'male' : 'female',
                'birth_date' => now()->subYears(20 + ($index % 25))->subDays($index)->toDateString(),
            ]);

            $categoryIds = collect($leafCategories)->shuffle()->take(4)->values();
            foreach ($categoryIds as $categoryId) {
                CustomerCategories::create([
                    'customer_id' => $customer->id,
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}
