<?php

namespace Database\Seeders;

use App\Helpers\Categories\CategoryTree;
use App\Models\Categories\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Oferly taxonomy: 11 main categories and 52 subcategories.
 *
 * One table, not two. The spec this came from describes separate
 * `main_categories` / `sub_categories` tables, but the app has a single
 * self-referencing `categories` table (parent_category_id), and the whole
 * interest-visibility rule is built on that one tree - see
 * App\Helpers\Categories\CategoryTree. A parent is a row with no
 * parent_category_id; a subcategory is a row that points at one.
 */
class CategoriesTableSeeder extends Seeder
{
    /**
     * Seed parent and child categories for offers/posts.
     */
    public function run()
    {
        $this->clearExistingTaxonomy();

        $order = 1;

        foreach ($this->tree() as $parentData) {
            $parent = Category::create([
                'parent_category_id' => null,
                'order' => $order++,
                'name_en' => $parentData['name_en'],
                'name_ar' => $parentData['name_ar'],
                'description' => $parentData['description'],
                'is_active' => true,
            ]);

            $childOrder = 1;

            foreach ($parentData['children'] as $child) {
                Category::create([
                    'parent_category_id' => $parent->id,
                    'order' => $childOrder++,
                    'name_en' => $child['name_en'],
                    'name_ar' => $child['name_ar'],
                    'description' => $child['description'] ?? null,
                    'is_active' => true,
                ]);
            }
        }

        //the truncate above bypassed the model events the observer listens on
        CategoryTree::flush();
    }

    /**
     * Drop the old taxonomy along with everything pointing at it.
     *
     * Truncating on its own is not enough: auto-increment restarts at 1, so a
     * post left tagged with the old category 2 would come back tagged with the
     * NEW category 2 - a restaurant post silently re-filed under mobile phones,
     * and a saved interest silently pointing at a category the user never chose.
     * Clearing the references is the honest version of "replace the taxonomy".
     *
     * @return void
     */
    private function clearExistingTaxonomy(): void
    {
        DB::table('posts')->whereNotNull('category_id')->update(['category_id' => null]);
        DB::table('offers')->whereNotNull('category_id')->update(['category_id' => null]);

        DB::table('customer_categories')->delete();
        DB::table('advertiser_categories')->delete();
        DB::table('advertiser_interests')->delete();

        Schema::disableForeignKeyConstraints();
        Category::truncate();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return array<int, array{name_en: string, name_ar: string, description: string, children: array<int, array{name_en: string, name_ar: string, description: string}>}>
     */
    private function tree(): array
    {
        return [
            [
                'name_en' => 'Electronics & Tech',
                'name_ar' => 'إلكترونيات وتكنولوجيا',
                'description' => 'عروض الأجهزة الذكية والحواسيب والأجهزة المنزلية وخدمات الصيانة',
                'children' => [
                    [
                        'name_en' => 'Mobile & Tablets',
                        'name_ar' => 'هواتف محمولة وأجهزة لوحية',
                        'description' => 'تخفيضات الهواتف، التابلت، الأجهزة الذكية',
                    ],
                    [
                        'name_en' => 'Computers & Laptops',
                        'name_ar' => 'أجهزة حواسيب ولابتوبات',
                        'description' => 'أجهزة كمبيوتر مكتبي، لابتوب، ملحقات البرمجة والغيمينغ',
                    ],
                    [
                        'name_en' => 'Accessories & Chargers',
                        'name_ar' => 'إكسسوارات إلكترونية وشواحن',
                        'description' => 'كابلات، شواحن، باور بنك، كفرات، حماية شاشة',
                    ],
                    [
                        'name_en' => 'Home Appliances',
                        'name_ar' => 'أجهزة منزلية وكهربائية',
                        'description' => 'شاشات، ثلاجات، غسالات، مكيفات، أدوات مطبخ إلكترونية',
                    ],
                    [
                        'name_en' => 'Electronics Repair & Parts',
                        'name_ar' => 'صيانة إلكترونيات وقطع غيار',
                        'description' => 'مراكز صيانة هواتف، حواسيب، شاشات، وقطع غيار',
                    ],
                    [
                        'name_en' => 'Security & Audio Systems',
                        'name_ar' => 'أنظمة حماية وصوتيات',
                        'description' => 'كاميرات مراقبة، مكبرات صوت، سماعات، أنظمة إنذار',
                    ],
                ],
            ],
            [
                'name_en' => 'Food & Dining',
                'name_ar' => 'مطاعم ومأكولات',
                'description' => 'عروض المطاعم والمقاهي والحلويات وخدمات إعداد الوجبات',
                'children' => [
                    [
                        'name_en' => 'Fast Food & Burgers',
                        'name_ar' => 'وجبات سريعة وبرغر',
                        'description' => 'مطاعم وجبات سريعة، برغر، شاورما، ساندويش',
                    ],
                    [
                        'name_en' => 'Oriental & Western Dining',
                        'name_ar' => 'مطاعم شرقية وغربية',
                        'description' => 'مطاعم وجبات عائلية، مأكولات شرقية وغربية',
                    ],
                    [
                        'name_en' => 'Cafes & Coffee Shops',
                        'name_ar' => 'مقاهي وكافيهات',
                        'description' => 'مشروبات ساخنة وباردة، كافيهات شبابية وعائلية',
                    ],
                    [
                        'name_en' => 'Sweets & Bakery',
                        'name_ar' => 'حلويات ومعجنات',
                        'description' => 'حلويات عربية وغربية، كيك، كريب، وافل، مخابز',
                    ],
                    [
                        'name_en' => 'Juices & Ice Cream',
                        'name_ar' => 'عصائر وآيس كريم',
                        'description' => 'عصائر طبيعية، كوكتيل، آيس كريم، موشيز',
                    ],
                    [
                        'name_en' => 'Cloud Kitchens & Catering',
                        'name_ar' => 'مطابخ سحابية وإعداد وجبات',
                        'description' => 'وجبات دايت، إعداد بوفيهات ومناسبات',
                    ],
                ],
            ],
            [
                'name_en' => 'Fashion & Apparel',
                'name_ar' => 'أزياء وموضة',
                'description' => 'عروض الألبسة والأحذية والإكسسوارات وخدمات الخياطة',
                'children' => [
                    [
                        'name_en' => "Men's Clothing",
                        'name_ar' => 'ألبسة رجالية',
                        'description' => 'بدل، قمصان، ملابس كاجوال، ملابس رياضية رجالية',
                    ],
                    [
                        'name_en' => "Women's Clothing & Hijab",
                        'name_ar' => 'ألبسة نسائية ومحجبات',
                        'description' => 'فساتين، أزياء يومية، عبايات، ملابس محجبات',
                    ],
                    [
                        'name_en' => 'Kids & Babies Clothing',
                        'name_ar' => 'ألبسة أطفال ومواليد',
                        'description' => 'ملابس أطفال، حديثي الولادة، مستلزمات أطفال',
                    ],
                    [
                        'name_en' => 'Shoes & Bags',
                        'name_ar' => 'أحذية وحقائب',
                        'description' => 'أحذية رسمية ورياضية، حقائب نسائية وسفر',
                    ],
                    [
                        'name_en' => 'Accessories & Jewelry',
                        'name_ar' => 'إكسسوارات ومجوهرات',
                        'description' => 'ساعات، مجوهرات فضية وذهبية، نظارات، إكسسوارات',
                    ],
                    [
                        'name_en' => 'Tailoring & Alterations',
                        'name_ar' => 'خياطة وتعديل أزياء',
                        'description' => 'مخازن خياطة وتفصيل وتعديل ملابس',
                    ],
                ],
            ],
            [
                'name_en' => 'Health & Beauty',
                'name_ar' => 'صحة وجمال',
                'description' => 'عروض الصيدليات والعيادات وصالونات ومراكز التجميل',
                'children' => [
                    [
                        'name_en' => 'Pharmacies & Medical Supplies',
                        'name_ar' => 'صيدليات ومستلزمات طبية',
                        'description' => 'أدوية، فيتامينات، معدات طبية منزلية',
                    ],
                    [
                        'name_en' => 'Salons & Barbershops',
                        'name_ar' => 'صالونات حلاقة وتصفيف شعر',
                        'description' => 'حلاقة رجالي، صالونات تجميل نسائية',
                    ],
                    [
                        'name_en' => 'Skincare & Cosmetics',
                        'name_ar' => 'عناية بالبشرة ومستحضرات تجميل',
                        'description' => 'مكياج، كريمات عناية، عطور، منتجات عناية',
                    ],
                    [
                        'name_en' => 'Beauty & Laser Centers',
                        'name_ar' => 'مراكز تجميل وليزر',
                        'description' => 'جلسات ليزر، تنظيف بشرة، عناية بالجسد',
                    ],
                    [
                        'name_en' => 'Medical Clinics',
                        'name_ar' => 'عيادات ومراكز طبية تخصصية',
                        'description' => 'عيادات أسنان، جلدية، تغذية، علاج طبيعي',
                    ],
                    [
                        'name_en' => 'Optics & Eyewear',
                        'name_ar' => 'بصريات ونظارات',
                        'description' => 'فحص نظر، نظارات طبية وشمسية، عدسات',
                    ],
                ],
            ],
            [
                'name_en' => 'Sports & Fitness',
                'name_ar' => 'رياضة ولياقة بدنية',
                'description' => 'عروض الأندية والأكاديميات والمعدات والمكملات الرياضية',
                'children' => [
                    [
                        'name_en' => 'Gyms & Fitness Centers',
                        'name_ar' => 'أندية وأكاديميات رياضية',
                        'description' => 'اشتراكات نوادي رياضية، أكاديميات تدريب',
                    ],
                    [
                        'name_en' => 'Sports Equipment',
                        'name_ar' => 'معدات ومستلزمات رياضية',
                        'description' => 'أجهزة لياقة منزلية، أثقال، أدوات رياضية',
                    ],
                    [
                        'name_en' => 'Martial Arts & Equestrian',
                        'name_ar' => 'أكاديميات فنون قتالية وفروسية',
                        'description' => 'كاراتيه، تايكوندو، قتال حر، ركوب خيل',
                    ],
                    [
                        'name_en' => 'Supplements & Nutrition',
                        'name_ar' => 'مكملات غذائية وبروتين',
                        'description' => 'بروتينات، مكملات رياضية، مكملات صحية',
                    ],
                ],
            ],
            [
                'name_en' => 'Education & Training',
                'name_ar' => 'تعليم وتدريب',
                'description' => 'عروض المراكز والمعاهد والمدارس والمستلزمات الدراسية',
                'children' => [
                    [
                        'name_en' => 'Training & Language Centers',
                        'name_ar' => 'مراكز تدريب ولغات',
                        'description' => 'دورات لغات، مهارات إدارية، تسويق، برمجة',
                    ],
                    [
                        'name_en' => 'Institutes & Tutoring',
                        'name_ar' => 'معاهد ودروس خاصة',
                        'description' => 'تقوية مدرسية، معاهد جامعية، تحضير امتحانات',
                    ],
                    [
                        'name_en' => 'Schools & Kindergartens',
                        'name_ar' => 'مدارس وروضات أطفال',
                        'description' => 'روضات أطفال، مدارس خاصة، حضانات نموذجية',
                    ],
                    [
                        'name_en' => 'Stationery & Bookstores',
                        'name_ar' => 'مكتبات ومستلزمات دراسية',
                        'description' => 'كتب، قرطاسية، أدوات مكتبية ورسم',
                    ],
                ],
            ],
            [
                'name_en' => 'Automotive & Vehicles',
                'name_ar' => 'سيارات ومركبات',
                'description' => 'عروض الصيانة وقطع الغيار والتأجير والدراجات النارية',
                'children' => [
                    [
                        'name_en' => 'Auto Service & Car Wash',
                        'name_ar' => 'صيانة ومغاسل سيارات',
                        'description' => 'ميكانيك، كهرباء سيارات، غسيل وتلميع',
                    ],
                    [
                        'name_en' => 'Auto Parts & Accessories',
                        'name_ar' => 'قطع غيار وإكسسوارات سيارات',
                        'description' => 'إطارات، بطاريات، زيوت، فرش وتجهيزات',
                    ],
                    [
                        'name_en' => 'Car Rental',
                        'name_ar' => 'تأجير ومكاتب سيارات',
                        'description' => 'تأجير سيارات سياحية ونقل',
                    ],
                    [
                        'name_en' => 'Motorcycles & Bikes',
                        'name_ar' => 'دراجات نارية وهوائية',
                        'description' => 'بيع وصيانة دراجات نارية وهوائية',
                    ],
                ],
            ],
            [
                'name_en' => 'Home & Living',
                'name_ar' => 'مستلزمات منزل وديكور',
                'description' => 'عروض الأثاث والديكور والأدوات المنزلية ومواد التنظيف',
                'children' => [
                    [
                        'name_en' => 'Furniture & Furnishings',
                        'name_ar' => 'أثاث ومفروشات',
                        'description' => 'غرف نوم، صالونات، أثاث مكتبي ومنزلي',
                    ],
                    [
                        'name_en' => 'Decor & Lighting',
                        'name_ar' => 'ديكورات وإضاءة',
                        'description' => 'ثريات، تحف، إضاءة LED، ورق جدران',
                    ],
                    [
                        'name_en' => 'Kitchenware & Housewares',
                        'name_ar' => 'أدوات منزلية ومطبخ',
                        'description' => 'أواني طبخ، أدوات مائدة، مستلزمات ضيافة',
                    ],
                    [
                        'name_en' => 'Cleaning Supplies',
                        'name_ar' => 'مستلزمات تنظيف ومطهرات',
                        'description' => 'منظفات منزلية، أدوات تعقيم وتطهير',
                    ],
                ],
            ],
            [
                'name_en' => 'Business & Services',
                'name_ar' => 'خدمات أعمال ومهن',
                'description' => 'عروض الدعاية والبرمجة والخدمات العقارية والقانونية',
                'children' => [
                    [
                        'name_en' => 'Advertising & Media',
                        'name_ar' => 'دعاية وتصميم واستديوهات',
                        'description' => 'طباعة، تصميم، تصوير فوتوغرافي، فيديو',
                    ],
                    [
                        'name_en' => 'Software & IT Solutions',
                        'name_ar' => 'برمجة وحلول تقنية',
                        'description' => 'تطوير مواقع، شبكات، صيانة أنظمة برمجية',
                    ],
                    [
                        'name_en' => 'Real Estate & Engineering',
                        'name_ar' => 'خدمات عقارية وهندسية',
                        'description' => 'مكاتب عقارية، استشارات هندسية وديكور',
                    ],
                    [
                        'name_en' => 'Legal & Accounting Services',
                        'name_ar' => 'خدمات قانونية ومحاسبية',
                        'description' => 'محاسبة، استشارات قانونية، تعقيب معاملات',
                    ],
                ],
            ],
            [
                'name_en' => 'Tourism & Entertainment',
                'name_ar' => 'سياحة وترفيه ومناسبات',
                'description' => 'عروض السفر والفنادق وتنظيم المناسبات ومراكز الترفيه',
                'children' => [
                    [
                        'name_en' => 'Travel & Tourism Agencies',
                        'name_ar' => 'مكاتب سفر وسياحة',
                        'description' => 'حجز طيران، رحلات سياحية، فيز وتأشيرات',
                    ],
                    [
                        'name_en' => 'Hotels & Resorts',
                        'name_ar' => 'فنادق ومنتجعات وشاليهات',
                        'description' => 'إقامات فندقية، حجز شاليهات واستراحات',
                    ],
                    [
                        'name_en' => 'Event Planning & Catering',
                        'name_ar' => 'تنظيم حفلات ومناسبات',
                        'description' => 'قاعات أفراح، كوشة، تنظيم مؤتمرات وحفلات',
                    ],
                    [
                        'name_en' => 'Gaming & Entertainment Centers',
                        'name_ar' => 'ألعاب ومراكز ترفيه',
                        'description' => 'صالات ألعاب، ملاهي أطفال، صالات بلايستيشن',
                    ],
                ],
            ],
            [
                'name_en' => 'Supermarket & Groceries',
                'name_ar' => 'سوبرماركت ومواد غذائية',
                'description' => 'عروض السوبرماركت واللحوم والخضار والألبان والمحامص',
                'children' => [
                    [
                        'name_en' => 'Supermarket & Hypermarket',
                        'name_ar' => 'سوبرماركت ومول غذائي',
                        'description' => 'تخفيضات المواد الغذائية والاستهلاكية اليومية',
                    ],
                    [
                        'name_en' => 'Meat, Poultry & Fish',
                        'name_ar' => 'لحوم ودواجن وأسماك',
                        'description' => 'ملحمة، دواجن طازجة، أسماك ومأكولات بحرية',
                    ],
                    [
                        'name_en' => 'Fresh Fruits & Vegetables',
                        'name_ar' => 'خضار وفواكه طازجة',
                        'description' => 'محلات خضار وفواكه، منتجات عضوية',
                    ],
                    [
                        'name_en' => 'Dairy & Roastery',
                        'name_ar' => 'ألبان وأجبان ومحمصة',
                        'description' => 'محامص، مكسرات، بهارات، ألبان وأجبان بلدية',
                    ],
                ],
            ],
        ];
    }
}
