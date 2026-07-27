<?php

namespace Database\Seeders;

use App\Models\Advertisements\Advertisement;
use App\Models\Advertisements\Side\SideAdvertisement;
use App\Models\Advertisements\Slider\SliderAdvertisement;
use App\Models\Categories\Category;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Modals\Modal;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Offers\Likes\OfferLikes;
use App\Models\Offers\Offer;
use App\Models\Offers\Ratings\OfferRatings;
use App\Models\Posts\Comments\PostComments;
use App\Models\Posts\Likes\PostLikes;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
use App\Models\Users\Customers\CustomerUser;
use App\Models\Users\Shared\Followings\UserFollowings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoContentSeeder extends Seeder
{
    /**
     * Seed offers, posts, ads, engagement, and reports.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        OfferLikes::truncate();
        OfferRatings::truncate();
        OffersComments::truncate();
        PostLikes::truncate();
        PostComments::truncate();
        AdvertiserRatings::truncate();
        UserFollowings::truncate();
        Report::truncate();
        Offer::truncate();
        Post::truncate();
        Advertisement::truncate();
        SliderAdvertisement::truncate();
        SideAdvertisement::truncate();
        Modal::truncate();
        Schema::enableForeignKeyConstraints();

        $advertisers = AdvertiserUser::orderBy('id')->get();
        $customers = CustomerUser::orderBy('id')->get();
        $categories = Category::whereNotNull('parent_category_id')->get();
        $country = Country::where('code', 'SY')->first();
        $governorateIds = Governorate::where('country_code', 'SY')->pluck('id')->all();
        $cityIds = City::pluck('id')->all();
        $now = now();

        $advertiserClass = AdvertiserUser::class;
        $customerClass = CustomerUser::class;

        $offerTemplates = [
            'خصم خاص لفترة محدودة في %s — لا تفوّت العرض!',
            'عرض اليوم في %s: تخفيضات تصل إلى نسبة كبيرة على المنتجات المختارة.',
            'اشترِ الآن من %s واحصل على مفاجأة مع كل طلب.',
            'أفضل الأسعار في %s لهذا الأسبوع فقط.',
            'عرض عائلي مميز من %s — مناسب لكل أفراد العائلة.',
        ];

        $offers = collect();
        for ($i = 0; $i < 100; $i++) {
            $advertiser = $advertisers[$i % $advertisers->count()];
            $category = $categories[$i % $categories->count()];
            $sale = [10, 15, 20, 25, 30, 40, 50][$i % 7];
            $status = $i % 12 === 0 ? 'pending' : ($i % 20 === 0 ? 'unapproved' : 'approved');

            $offer = Offer::create([
                'category_id' => $category->id,
                'advertiser_id' => $advertiser->id,
                'content' => sprintf($offerTemplates[$i % count($offerTemplates)], $advertiser->name),
                'sale_percentage' => $sale,
                'advertisement_url' => 'https://awfarly.test/offers/' . ($i + 1),
                'rate' => round(3 + (($i % 20) / 10), 1),
                'expires_at' => $now->copy()->addDays(7 + ($i % 30)),
                'expires_in' => 7 + ($i % 30),
                'status' => $status,
                'views_count' => 20 + ($i * 3),
                'likes_count' => 0,
                'comments_count' => 0,
                'amount' => 1000 + ($i * 150),
                'currency' => 'SYP',
            ]);
            $offers->push($offer);
        }

        $postTemplates = [
            'منشور جديد من مجتمع أوفرلي حول أفضل العروض في سوريا.',
            'شاركنا تجربتك مع المحلات المحلية في مدينتك.',
            'نصيحة اليوم: قارن العروض قبل الشراء لتوفّر أكثر.',
            'افتتاح فرع جديد وتخفيضات ترحيبية للزبائن.',
            'ماذا تفضّل أكثر: عروض المطاعم أم الإلكترونيات؟',
        ];

        $posts = collect();
        for ($i = 0; $i < 50; $i++) {
            $isAdvertiserPost = $i % 3 !== 0;
            $user = $isAdvertiserPost
                ? $advertisers[$i % $advertisers->count()]
                : $customers[$i % $customers->count()];
            $category = $categories[$i % $categories->count()];
            $status = $i % 10 === 0 ? 'pending' : 'approved';

            $post = Post::create([
                'user_type' => $isAdvertiserPost ? $advertiserClass : $customerClass,
                'user_id' => $user->id,
                'category_id' => $category->id,
                'content' => $postTemplates[$i % count($postTemplates)] . ' #' . ($i + 1),
                'status' => $status,
                'views_count' => 15 + ($i * 2),
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => $i % 5,
            ]);
            $posts->push($post);
        }

        for ($i = 0; $i < 12; $i++) {
            Advertisement::create([
                'type' => ['any', 'website', 'mobile'][$i % 3],
                'users' => ['any', 'customers', 'advertisers'][$i % 3],
                'advertiser_name' => $advertisers[$i % $advertisers->count()]->name,
                'advertiser_url' => 'https://awfarly.test/ads/' . ($i + 1),
                'content' => 'إعلان ترويجي تجريبي رقم ' . ($i + 1) . ' موجه للمستخدمين في سوريا.',
                'categories' => $categories->random(min(3, $categories->count()))->pluck('id')->values()->all(),
                'countries' => [$country->id],
                'governorates' => collect($governorateIds)->random(min(4, count($governorateIds)))->values()->all(),
                'cities' => collect($cityIds)->random(min(4, count($cityIds)))->values()->all(),
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(20 + $i),
                'is_active' => $i < 9,
            ]);
        }

        for ($i = 0; $i < 6; $i++) {
            SliderAdvertisement::create([
                'advertisement_url' => 'https://awfarly.test/slider/' . ($i + 1),
                'starts_at' => $now->copy()->subDay(),
                'ends_at' => $now->copy()->addDays(15 + $i),
            ]);

            SideAdvertisement::create([
                'advertisement_url' => 'https://awfarly.test/side/' . ($i + 1),
                'side' => ['right', 'left', 'both'][$i % 3],
                'starts_at' => $now->copy()->subDay(),
                'ends_at' => $now->copy()->addDays(12 + $i),
            ]);
        }

        Modal::insert([
            [
                'recipients_type' => 'all',
                'title_ar' => 'مرحباً بك في أوفرلي',
                'title_en' => 'Welcome to Awfarly',
                'body_ar' => 'اكتشف أفضل العروض بالقرب منك في المحافظات السورية.',
                'body_en' => 'Discover the best offers near you across Syrian governorates.',
                'link' => 'https://awfarly.com',
                'start_at' => $now->copy()->subDay(),
                'end_at' => $now->copy()->addMonth(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'recipients_type' => 'customers',
                'title_ar' => 'عروض جديدة هذا الأسبوع',
                'title_en' => 'New offers this week',
                'body_ar' => 'تصفح أحدث التخفيضات من المعلنين المحليين.',
                'body_en' => 'Browse the latest discounts from local advertisers.',
                'link' => null,
                'start_at' => $now->copy()->subDay(),
                'end_at' => $now->copy()->addWeeks(2),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Engagement: likes, comments, ratings, followings
        foreach ($offers->take(80) as $index => $offer) {
            $likers = $customers->random(min(5, $customers->count()));
            foreach ($likers as $customer) {
                OfferLikes::firstOrCreate([
                    'offer_id' => $offer->id,
                    'user_type' => $customerClass,
                    'user_id' => $customer->id,
                ]);
            }
            $offer->update(['likes_count' => OfferLikes::where('offer_id', $offer->id)->count()]);

            if ($index % 2 === 0) {
                OffersComments::create([
                    'offer_id' => $offer->id,
                    'user_type' => $customerClass,
                    'user_id' => $customers[$index % $customers->count()]->id,
                    'comment' => 'عرض ممتاز، شكراً لكم!',
                    'likes_count' => rand(0, 8),
                ]);
                $offer->increment('comments_count');
            }

            if ($index % 3 === 0) {
                OfferRatings::create([
                    'offer_id' => $offer->id,
                    'user_type' => $customerClass,
                    'user_id' => $customers[($index + 3) % $customers->count()]->id,
                    'comment' => 'تقييم تجريبي للعرض.',
                    'rate' => 3 + ($index % 3),
                    'status' => 'approved',
                ]);
            }
        }

        foreach ($posts->take(40) as $index => $post) {
            $likers = $customers->random(min(4, $customers->count()));
            foreach ($likers as $customer) {
                PostLikes::firstOrCreate([
                    'post_id' => $post->id,
                    'user_type' => $customerClass,
                    'user_id' => $customer->id,
                ]);
            }
            $post->update(['likes_count' => PostLikes::where('post_id', $post->id)->count()]);

            if ($index % 2 === 0) {
                PostComments::create([
                    'post_id' => $post->id,
                    'user_type' => $customerClass,
                    'user_id' => $customers[$index % $customers->count()]->id,
                    'comment' => 'تعليق تجريبي على المنشور.',
                    'likes_count' => rand(0, 5),
                ]);
                $post->increment('comments_count');
            }
        }

        foreach ($advertisers->take(20) as $index => $advertiser) {
            AdvertiserRatings::create([
                'advertiser_id' => $advertiser->id,
                'user_type' => $customerClass,
                'user_id' => $customers[$index % $customers->count()]->id,
                'comment' => 'تجربة جيدة مع هذا المعلن.',
                'rate' => 3.5 + (($index % 3) * 0.5),
                'status' => 'approved',
            ]);

            UserFollowings::create([
                'followed_type' => $advertiserClass,
                'followed_id' => $advertiser->id,
                'follower_type' => $customerClass,
                'follower_id' => $customers[$index % $customers->count()]->id,
                'status' => 'approved',
            ]);
        }

        $reportTypes = [
            'Spam', 'Misleading or Scam', 'Offensive', 'Other', 'False News',
        ];

        for ($i = 0; $i < 15; $i++) {
            $offer = $offers[$i % $offers->count()];
            Report::create([
                'type' => $reportTypes[$i % count($reportTypes)],
                'user_type' => $customerClass,
                'user_id' => $customers[$i % $customers->count()]->id,
                'reported_type' => Offer::class,
                'reported_id' => $offer->id,
                'reason' => 'بلاغ تجريبي للمراجعة من لوحة الإدارة.',
                'status' => $i % 4 === 0 ? 'solved' : 'pending',
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            Report::create([
                'type' => $reportTypes[$i % count($reportTypes)],
                'user_type' => $customerClass,
                'user_id' => $customers[($i + 5) % $customers->count()]->id,
                'reported_type' => $advertiserClass,
                'reported_id' => $advertisers[$i % $advertisers->count()]->id,
                'reason' => 'بلاغ تجريبي عن معلن.',
                'status' => 'pending',
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            Report::create([
                'type' => $reportTypes[$i % count($reportTypes)],
                'user_type' => $customerClass,
                'user_id' => $customers[($i + 8) % $customers->count()]->id,
                'reported_type' => Post::class,
                'reported_id' => $posts[$i % $posts->count()]->id,
                'reason' => 'بلاغ تجريبي عن منشور.',
                'status' => 'pending',
            ]);
        }
    }
}
