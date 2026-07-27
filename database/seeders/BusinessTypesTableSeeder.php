<?php

namespace Database\Seeders;

use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BusinessTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        AdvertiserBusinessType::truncate();
        Schema::enableForeignKeyConstraints();

        AdvertiserBusinessType::insert([
            [
                'order' => 1,
                'name_en' => 'Company',
                'name_ar' => 'شركة',
                'is_active' => true,
            ],
            [
                'order' => 1,
                'name_en' => 'Factory',
                'name_ar' => 'مصنع',
                'is_active' => true,
            ],
            [
                'order' => 1,
                'name_en' => 'Corporation',
                'name_ar' => 'مؤسسة',
                'is_active' => true,
            ],
            [
                'order' => 1,
                'name_en' => 'Gallery',
                'name_ar' => 'معرض',
                'is_active' => true,
            ],
            [
                'order' => 1,
                'name_en' => 'Workshop',
                'name_ar' => 'ورشة',
                'is_active' => true,
            ],
            [
                'order' => 1,
                'name_en' => 'Office',
                'name_ar' => 'مكتب',
                'is_active' => true,
            ],
        ]);
    }
}
