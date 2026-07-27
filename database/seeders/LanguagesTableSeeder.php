<?php

namespace Database\Seeders;

use App\Models\Languages\Language;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::query()->delete();

        Language::insert([
            [
                'name' => 'English',
                'code' => 'en',
                'image' => 'assets/images/flags/gb.png',
                'direction' => 'ltr',
                'is_default' => true,
            ],
            [
                'name' => 'العربية',
                'code' => 'ar',
                'image' => 'assets/images/flags/sa.png',
                'direction' => 'rtl',
                'is_default' => false,
            ],
        ]);
    }
}
