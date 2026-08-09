<?php

namespace Database\Seeders;

use App\Models\Interests\Interest;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Interests\AdvertiserInterests;
use App\Models\Users\Customers\CustomerUser;
use App\Models\Users\Customers\Interests\CustomerInterests;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class InterestsTableSeeder extends Seeder
{
    /**
     * Seed 30 flat interests and attach a random subset of them to every existing advertiser/customer.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        AdvertiserInterests::truncate();
        CustomerInterests::truncate();
        Interest::truncate();
        Schema::enableForeignKeyConstraints();

        $interests = [
            ['name_en' => 'Sports', 'name_ar' => 'رياضة'],
            ['name_en' => 'Technology', 'name_ar' => 'تقنية'],
            ['name_en' => 'Travel', 'name_ar' => 'سفر'],
            ['name_en' => 'Cooking', 'name_ar' => 'طبخ'],
            ['name_en' => 'Fashion', 'name_ar' => 'موضة'],
            ['name_en' => 'Cars', 'name_ar' => 'سيارات'],
            ['name_en' => 'Gaming', 'name_ar' => 'ألعاب'],
            ['name_en' => 'Music', 'name_ar' => 'موسيقى'],
            ['name_en' => 'Photography', 'name_ar' => 'تصوير'],
            ['name_en' => 'Reading', 'name_ar' => 'قراءة'],
            ['name_en' => 'Movies & TV', 'name_ar' => 'أفلام ومسلسلات'],
            ['name_en' => 'Fitness', 'name_ar' => 'لياقة بدنية'],
            ['name_en' => 'Art & Design', 'name_ar' => 'فن وتصميم'],
            ['name_en' => 'Real Estate', 'name_ar' => 'عقارات'],
            ['name_en' => 'Beauty & Skincare', 'name_ar' => 'جمال وعناية بالبشرة'],
            ['name_en' => 'Home & Furniture', 'name_ar' => 'منزل وأثاث'],
            ['name_en' => 'Parenting', 'name_ar' => 'تربية الأطفال'],
            ['name_en' => 'Pets', 'name_ar' => 'حيوانات أليفة'],
            ['name_en' => 'Health & Wellness', 'name_ar' => 'صحة وعافية'],
            ['name_en' => 'Education', 'name_ar' => 'تعليم'],
            ['name_en' => 'Business & Entrepreneurship', 'name_ar' => 'أعمال وريادة'],
            ['name_en' => 'Finance & Investing', 'name_ar' => 'مالية واستثمار'],
            ['name_en' => 'Handmade & Crafts', 'name_ar' => 'أشغال يدوية'],
            ['name_en' => 'Electronics', 'name_ar' => 'إلكترونيات'],
            ['name_en' => 'Outdoor & Camping', 'name_ar' => 'تخييم وأنشطة خارجية'],
            ['name_en' => 'Coffee & Cafes', 'name_ar' => 'قهوة ومقاهي'],
            ['name_en' => 'Gardening', 'name_ar' => 'بستنة'],
            ['name_en' => 'Charity & Volunteering', 'name_ar' => 'خير وتطوع'],
            ['name_en' => 'Religion', 'name_ar' => 'دين'],
            ['name_en' => 'Nightlife & Events', 'name_ar' => 'سهرات ومناسبات'],
        ];

        $order = 1;
        $records = [];
        foreach ($interests as $interest) {
            $records[] = Interest::create([
                'order' => $order++,
                'name_en' => $interest['name_en'],
                'name_ar' => $interest['name_ar'],
                'is_active' => true,
            ]);
        }

        $interestIds = collect($records)->pluck('id')->all();

        AdvertiserUser::all()->each(function (AdvertiserUser $advertiser) use ($interestIds) {
            foreach ((array) array_rand(array_flip($interestIds), min(count($interestIds), rand(3, 6))) as $interestId) {
                AdvertiserInterests::create([
                    'advertiser_id' => $advertiser->id,
                    'interest_id' => $interestId,
                ]);
            }
        });

        CustomerUser::all()->each(function (CustomerUser $customer) use ($interestIds) {
            foreach ((array) array_rand(array_flip($interestIds), min(count($interestIds), rand(3, 6))) as $interestId) {
                CustomerInterests::create([
                    'customer_id' => $customer->id,
                    'interest_id' => $interestId,
                ]);
            }
        });
    }
}
