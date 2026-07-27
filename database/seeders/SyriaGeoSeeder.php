<?php

namespace Database\Seeders;

use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SyriaGeoSeeder extends Seeder
{
    /**
     * Seed Syria, 14 governorates, and sample cities under each.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        City::truncate();
        Governorate::truncate();
        Country::truncate();
        Schema::enableForeignKeyConstraints();

        $now = now();

        Country::create([
            'order' => 1,
            'code' => 'SY',
            'name_ar' => 'سوريا',
            'name_en' => 'Syria',
            'mobile_code' => '+963',
            'is_active' => true,
        ]);

        $governorates = [
            ['order' => 1, 'name_ar' => 'دمشق', 'name_en' => 'Damascus', 'cities' => [
                ['الميدان', 'Al-Midan'],
                ['باب سريجة', 'Bab Sarijah'],
                ['المزة', 'Al-Mezzeh'],
                ['كفرسوسة', 'Kafr Sousa'],
                ['جوبر', 'Jobar'],
                ['ركن الدين', 'Rukn al-Din'],
            ]],
            ['order' => 2, 'name_ar' => 'ريف دمشق', 'name_en' => 'Rif Dimashq', 'cities' => [
                ['حرستا', 'Harasta'],
                ['داريا', 'Darayya'],
                ['دوما', 'Douma'],
                ['الزبداني', 'Al-Zabadani'],
                ['قطنا', 'Qatana'],
            ]],
            ['order' => 3, 'name_ar' => 'حلب', 'name_en' => 'Aleppo', 'cities' => [
                ['السليمانية', 'Al-Sulaymaniyah'],
                ['الفرقان', 'Al-Furqan'],
                ['الأعظمية', 'Al-Azamiyah'],
                ['الجديد', 'Al-Jadid'],
                ['صلاح الدين', 'Salah al-Din'],
            ]],
            ['order' => 4, 'name_ar' => 'حمص', 'name_en' => 'Homs', 'cities' => [
                ['الوعر', 'Al-Waer'],
                ['بابا عمرو', 'Baba Amr'],
                ['الخالدية', 'Al-Khalidiyah'],
                ['الإنشاءات', 'Al-Inshaat'],
            ]],
            ['order' => 5, 'name_ar' => 'حماة', 'name_en' => 'Hama', 'cities' => [
                ['الحاضر', 'Al-Hadher'],
                ['البرزة', 'Al-Barzah'],
                ['الصابونية', 'Al-Sabouniyah'],
                ['الشريعة', 'Al-Sharia'],
            ]],
            ['order' => 6, 'name_ar' => 'اللاذقية', 'name_en' => 'Latakia', 'cities' => [
                ['المشروع السابع', 'Project 7'],
                ['الرمل الشمالي', 'North Raml'],
                ['الزراعة', 'Al-Ziraa'],
                ['سقوبين', 'Sqoubeen'],
            ]],
            ['order' => 7, 'name_ar' => 'طرطوس', 'name_en' => 'Tartus', 'cities' => [
                ['المنطقة الصناعية', 'Industrial Zone'],
                ['الحمرات', 'Al-Hamrat'],
                ['الشيخ بدر', 'Sheikh Badr'],
            ]],
            ['order' => 8, 'name_ar' => 'إدلب', 'name_en' => 'Idlib', 'cities' => [
                ['معرة النعمان', 'Maarat al-Numan'],
                ['سراقب', 'Saraqib'],
                ['جسر الشغور', 'Jisr al-Shughur'],
            ]],
            ['order' => 9, 'name_ar' => 'دير الزور', 'name_en' => 'Deir ez-Zor', 'cities' => [
                ['الجورة', 'Al-Joura'],
                ['القصور', 'Al-Qusour'],
                ['موحسن', 'Muhasan'],
            ]],
            ['order' => 10, 'name_ar' => 'الرقة', 'name_en' => 'Raqqa', 'cities' => [
                ['الثورة', 'Al-Thawrah'],
                ['الدرعية', 'Al-Dariyah'],
                ['معدان', 'Maadan'],
            ]],
            ['order' => 11, 'name_ar' => 'الحسكة', 'name_en' => 'Al-Hasakah', 'cities' => [
                ['القامشلي', 'Qamishli'],
                ['رأس العين', 'Ras al-Ayn'],
                ['المالكية', 'Al-Malikiyah'],
            ]],
            ['order' => 12, 'name_ar' => 'درعا', 'name_en' => 'Daraa', 'cities' => [
                ['الصنمين', 'Al-Sanamayn'],
                ['ازرع', 'Izra'],
                ['نوى', 'Nawa'],
            ]],
            ['order' => 13, 'name_ar' => 'السويداء', 'name_en' => 'As-Suwayda', 'cities' => [
                ['صلخد', 'Salkhad'],
                ['شهبا', 'Shahba'],
                ['القريا', 'Al-Quraya'],
            ]],
            ['order' => 14, 'name_ar' => 'القنيطرة', 'name_en' => 'Quneitra', 'cities' => [
                ['خان أرنبة', 'Khan Arnaba'],
                ['جباثا الزيت', 'Jubatha al-Zayt'],
                ['مسحرة', 'Mashara'],
            ]],
        ];

        foreach ($governorates as $item) {
            $governorate = Governorate::create([
                'order' => $item['order'],
                'country_code' => 'SY',
                'name_ar' => $item['name_ar'],
                'name_en' => $item['name_en'],
            ]);

            $cityRows = [];
            foreach ($item['cities'] as $index => $city) {
                $cityRows[] = [
                    'order' => $index + 1,
                    'governorate_id' => $governorate->id,
                    'name_ar' => trim($city[0]),
                    'name_en' => $city[1],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            City::insert($cityRows);
        }
    }
}
