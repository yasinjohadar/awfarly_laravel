<?php

namespace Database\Seeders;

use App\Models\Categories\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Seed parent and child categories for offers/posts.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Category::truncate();
        Schema::enableForeignKeyConstraints();

        $tree = [
            [
                'name_en' => 'Food & Restaurants',
                'name_ar' => 'مطاعم وطعام',
                'description' => 'Restaurants, cafes, and food deals',
                'children' => [
                    ['name_en' => 'Restaurants', 'name_ar' => 'مطاعم'],
                    ['name_en' => 'Cafes', 'name_ar' => 'مقاهي'],
                    ['name_en' => 'Sweets', 'name_ar' => 'حلويات'],
                    ['name_en' => 'Fast Food', 'name_ar' => 'وجبات سريعة'],
                ],
            ],
            [
                'name_en' => 'Fashion & Beauty',
                'name_ar' => 'أزياء وتجميل',
                'description' => 'Clothing, beauty, and accessories',
                'children' => [
                    ['name_en' => 'Clothing', 'name_ar' => 'ملابس'],
                    ['name_en' => 'Shoes', 'name_ar' => 'أحذية'],
                    ['name_en' => 'Beauty Salons', 'name_ar' => 'صالونات تجميل'],
                    ['name_en' => 'Cosmetics', 'name_ar' => 'مستحضرات تجميل'],
                ],
            ],
            [
                'name_en' => 'Electronics',
                'name_ar' => 'إلكترونيات',
                'description' => 'Phones, computers, and gadgets',
                'children' => [
                    ['name_en' => 'Mobile Phones', 'name_ar' => 'هواتف محمولة'],
                    ['name_en' => 'Computers', 'name_ar' => 'حواسيب'],
                    ['name_en' => 'Accessories', 'name_ar' => 'إكسسوارات'],
                ],
            ],
            [
                'name_en' => 'Home & Furniture',
                'name_ar' => 'منزل وأثاث',
                'description' => 'Furniture and home supplies',
                'children' => [
                    ['name_en' => 'Furniture', 'name_ar' => 'أثاث'],
                    ['name_en' => 'Home Appliances', 'name_ar' => 'أجهزة منزلية'],
                    ['name_en' => 'Decor', 'name_ar' => 'ديكور'],
                ],
            ],
            [
                'name_en' => 'Health & Services',
                'name_ar' => 'صحة وخدمات',
                'description' => 'Clinics, pharmacies, and services',
                'children' => [
                    ['name_en' => 'Pharmacies', 'name_ar' => 'صيدليات'],
                    ['name_en' => 'Clinics', 'name_ar' => 'عيادات'],
                    ['name_en' => 'Education', 'name_ar' => 'تعليم'],
                    ['name_en' => 'Car Services', 'name_ar' => 'خدمات سيارات'],
                ],
            ],
            [
                'name_en' => 'Travel & Entertainment',
                'name_ar' => 'سفر وترفيه',
                'description' => 'Hotels, tourism, and leisure',
                'children' => [
                    ['name_en' => 'Hotels', 'name_ar' => 'فنادق'],
                    ['name_en' => 'Tourism', 'name_ar' => 'سياحة'],
                    ['name_en' => 'Sports', 'name_ar' => 'رياضة'],
                ],
            ],
        ];

        $order = 1;
        foreach ($tree as $parentData) {
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
                    'description' => null,
                    'is_active' => true,
                ]);
            }
        }
    }
}
