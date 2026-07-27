<?php

namespace Database\Seeders;

use App\Models\Subscriptions\Packages\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PackagesTableSeeder extends Seeder
{
    /**
     * Seed subscription packages for advertisers.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Package::truncate();
        Schema::enableForeignKeyConstraints();

        Package::insert([
            [
                'product_id' => 'awfarly.free',
                'name_en' => 'Free Starter',
                'name_ar' => 'الباقة المجانية',
                'maximum_posts' => 5,
                'maximum_offers' => 5,
                'maximum_monthly_offers' => 10,
                'maximum_points' => 50,
                'description_en' => 'Starter package for new Syrian advertisers.',
                'description_ar' => 'باقة بداية للمعلنين الجدد في سوريا.',
                'specifications_en' => json_encode(['5 posts', '5 offers', 'Basic support']),
                'specifications_ar' => json_encode(['5 منشورات', '5 عروض', 'دعم أساسي']),
                'price' => 0,
                'old_price' => null,
                'subscription_type' => 'monthly',
                'duration' => 30,
                'currency' => 'USD',
                'is_visible' => true,
                'is_active' => true,
                'is_trial' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 'awfarly.basic',
                'name_en' => 'Basic',
                'name_ar' => 'الأساسية',
                'maximum_posts' => 30,
                'maximum_offers' => 40,
                'maximum_monthly_offers' => 40,
                'maximum_points' => 300,
                'description_en' => 'For small shops and local businesses.',
                'description_ar' => 'مناسبة للمحلات والمشاريع الصغيرة.',
                'specifications_en' => json_encode(['30 posts', '40 offers', 'Priority listing']),
                'specifications_ar' => json_encode(['30 منشور', '40 عرض', 'ظهور محسّن']),
                'price' => 9.99,
                'old_price' => 14.99,
                'subscription_type' => 'monthly',
                'duration' => 30,
                'currency' => 'USD',
                'is_visible' => true,
                'is_active' => true,
                'is_trial' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 'awfarly.pro',
                'name_en' => 'Professional',
                'name_ar' => 'الاحترافية',
                'maximum_posts' => 100,
                'maximum_offers' => 150,
                'maximum_monthly_offers' => 150,
                'maximum_points' => 1000,
                'description_en' => 'For growing brands across Syrian cities.',
                'description_ar' => 'للعلامات التجارية النامية في المدن السورية.',
                'specifications_en' => json_encode(['100 posts', '150 offers', 'Elite badge']),
                'specifications_ar' => json_encode(['100 منشور', '150 عرض', 'شارة نخبة']),
                'price' => 24.99,
                'old_price' => 34.99,
                'subscription_type' => 'monthly',
                'duration' => 30,
                'currency' => 'USD',
                'is_visible' => true,
                'is_active' => true,
                'is_trial' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 'awfarly.elite',
                'name_en' => 'Elite Annual',
                'name_ar' => 'النخبة السنوية',
                'maximum_posts' => 500,
                'maximum_offers' => 700,
                'maximum_monthly_offers' => 700,
                'maximum_points' => 5000,
                'description_en' => 'Annual plan for large advertisers.',
                'description_ar' => 'خطة سنوية للمعلنين الكبار.',
                'specifications_en' => json_encode(['500 posts', '700 offers', 'Dedicated support']),
                'specifications_ar' => json_encode(['500 منشور', '700 عرض', 'دعم مخصص']),
                'price' => 199.99,
                'old_price' => 249.99,
                'subscription_type' => 'yearly',
                'duration' => 365,
                'currency' => 'USD',
                'is_visible' => true,
                'is_active' => true,
                'is_trial' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
