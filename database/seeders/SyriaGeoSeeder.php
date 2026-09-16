<?php

namespace Database\Seeders;

use App\Helpers\Geography\LocationTree;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Syria: the 14 governorates and 397 cities, towns and districts under them.
 *
 * This seeder syncs rather than truncates. Advertisers, customers, posts and
 * saved location interests all carry governorate_id / city_id, and truncating
 * restarts the auto-increment: an advertiser left pointing at the old city 7
 * would silently reappear in whichever city inherited id 7. So rows are matched
 * by name, kept where they still exist, and only what genuinely disappeared is
 * removed - with the references to it cleared first.
 */
class SyriaGeoSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $country = Country::updateOrCreate(
            ['code' => 'SY'],
            [
                'order' => 1,
                'name_ar' => 'سوريا',
                'name_en' => 'Syria',
                'mobile_code' => '+963',
                'is_active' => true,
            ]
        );

        $keptCityIds = [];

        foreach ($this->governorates() as $data) {
            //matched on `order` rather than a name: the names are exactly what a
            //re-run is meant to correct, so they cannot also be the key
            $governorate = Governorate::firstOrNew(['order' => $data['order']]);
            $governorate->fill([
                'country_code' => $country->code,
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
            ])->save();

            $existing = City::where('governorate_id', $governorate->id)
                ->get()
                ->keyBy(fn (City $city) => $this->normalize($city->name_ar));

            $order = 1;

            foreach ($data['cities'] as [$nameAr, $nameEn]) {
                $key = $this->normalize($nameAr);

                //normalized so a hamza that was typed differently (ازرع vs إزرع)
                //updates the existing row instead of creating a second one
                $city = $existing->get($key) ?? new City(['governorate_id' => $governorate->id]);
                $city->fill([
                    'governorate_id' => $governorate->id,
                    'order' => $order++,
                    'name_ar' => $nameAr,
                    'name_en' => $nameEn,
                ])->save();

                $keptCityIds[] = $city->id;
            }
        }

        $this->removeCitiesNotIn($keptCityIds);

        //the tree is cached, and some of the writes above were bulk deletes that
        //never reached the model observers
        LocationTree::flush();
    }

    /**
     * Drop the cities this seeder no longer knows about, after detaching
     * everything that points at them.
     *
     * A user or a post keeps its governorate_id - only the more specific city is
     * cleared - so nothing becomes unlocatable, it just becomes less precise.
     *
     * @param int[] $keptCityIds
     * @return void
     */
    private function removeCitiesNotIn(array $keptCityIds): void
    {
        $staleIds = City::whereNotIn('id', $keptCityIds)->pluck('id')->all();

        if (empty($staleIds)) {
            return;
        }

        DB::table('advertiser_preferred_cities')->whereIn('city_id', $staleIds)->delete();
        DB::table('customer_preferred_cities')->whereIn('city_id', $staleIds)->delete();

        DB::table('advertisers_users')->whereIn('city_id', $staleIds)->update(['city_id' => null]);
        DB::table('customers_users')->whereIn('city_id', $staleIds)->update(['city_id' => null]);
        DB::table('posts')->whereIn('city_id', $staleIds)->update(['city_id' => null]);

        City::whereIn('id', $staleIds)->delete();
    }

    /**
     * Fold the alef and yaa variants that the same place name gets typed with, so
     * matching an existing row does not depend on which one was used.
     *
     * @param string $name
     * @return string
     */
    private function normalize(string $name): string
    {
        return trim(str_replace(
            ['أ', 'إ', 'آ', 'ٱ', 'ى', 'ة', 'ـ'],
            ['ا', 'ا', 'ا', 'ا', 'ي', 'ه', ''],
            $name
        ));
    }

    /**
     * @return array<int, array{order: int, name_ar: string, name_en: string, cities: array<int, array{0: string, 1: string}>}>
     */
    private function governorates(): array
    {
        return [
            [
                'order' => 1,
                'name_ar' => 'دمشق',
                'name_en' => 'Damascus',
                'cities' => [
                    ['المزة', 'Al-Mazzeh'],
                    ['الميدان', 'Al-Midan'],
                    ['الشعلان', 'Al-Shaalan'],
                    ['أبو رمانة', 'Abu Rummaneh'],
                    ['البرامكة', 'Al-Baramkeh'],
                    ['القصاع', 'Al-Qassaa'],
                    ['باب توما', 'Bab Touma'],
                    ['ركن الدين', 'Rukn al-Din'],
                    ['السالية', 'Al-Saliyah'],
                    ['المالكي', 'Al-Maliki'],
                    ['الحمرا', 'Al-Hamra'],
                    ['باب شرقي', 'Bab Sharqi'],
                    ['السريان', 'Al-Suryan'],
                    ['القدم', 'Al-Qadam'],
                    ['القابون', 'Al-Qaboun'],
                    ['دمر', 'Dummar'],
                    ['كفرسوسة', 'Kafr Sousa'],
                    ['المهاجرين', 'Al-Muhajireen'],
                    ['صحن الشميسات', 'Sahn al-Shumaysat'],
                    ['ساروجة', 'Sarouja'],
                    ['العمارة', 'Al-Amara'],
                    ['الجسر الأبيض', 'Al-Jisr al-Abyad'],
                    ['الحلبوني', 'Al-Halbouni'],
                    ['القنوات', 'Al-Qanawat'],
                    ['جوبر', 'Jobar'],
                    ['الشاغور', 'Al-Shaghour'],
                    ['الربوة', 'Al-Rabweh'],
                    ['الجلاء', 'Al-Jalaa'],
                    ['الأمين', 'Al-Amin'],
                    ['الحجاز', 'Al-Hijaz'],
                    ['الحريقة', 'Al-Hariqa'],
                    ['أبو جرش', 'Abu Jarash'],
                    ['باب الجابية', 'Bab al-Jabiyeh'],
                    ['باب مصلى', 'Bab Musalla'],
                    ['باب سريجة', 'Bab Sarijah'],
                    ['الأمويين', 'Al-Umawiyeen'],
                    ['الزهراء الجديدة', 'New Al-Zahraa'],
                    ['العمرية', 'Al-Amariyah'],
                ],
            ],
            [
                'order' => 2,
                'name_ar' => 'ريف دمشق',
                'name_en' => 'Rif Dimashq',
                'cities' => [
                    ['دوما', 'Douma'],
                    ['داريا', 'Darayya'],
                    ['جرمانا', 'Jaramana'],
                    ['صحنايا', 'Sahnaya'],
                    ['أشرفية صحنايا', 'Ashrafiyat Sahnaya'],
                    ['السبينة', 'Al-Sabinah'],
                    ['خان الشيح', 'Khan al-Sheih'],
                    ['معضمية الشام', 'Moadamiyet al-Sham'],
                    ['دير علي', 'Deir Ali'],
                    ['الطيبة', 'Al-Taybeh'],
                    ['جديدة عرطوز', 'Jdeidet Artouz'],
                    ['زاكية', 'Zakyeh'],
                    ['سعسع', 'Saasaa'],
                    ['الكسوة', 'Al-Kiswah'],
                    ['الغزلانية', 'Al-Ghizlaniyah'],
                    ['الضمير', 'Al-Dumayr'],
                    ['قطنا', 'Qatana'],
                    ['الديماس', 'Al-Dimas'],
                    ['قدسيا', 'Qudsaya'],
                    ['الزبداني', 'Al-Zabadani'],
                    ['بلودان', 'Bloudan'],
                    ['مضايا', 'Madaya'],
                    ['سرغايا', 'Sarghaya'],
                    ['عسال الورد', 'Assal al-Ward'],
                    ['معلولا', 'Maaloula'],
                    ['صيدنايا', 'Saidnaya'],
                    ['التل', 'Al-Tall'],
                    ['عين منين', 'Ain Munin'],
                    ['رنكوس', 'Rankous'],
                    ['القطيفة', 'Al-Qutayfah'],
                    ['الرحيبة', 'Al-Ruhaybah'],
                    ['جيرود', 'Jayroud'],
                    ['يبرود', 'Yabroud'],
                    ['النبك', 'Al-Nabk'],
                    ['دير عطية', 'Deir Atiyah'],
                    ['قارة', 'Qarah'],
                    ['حرستا', 'Harasta'],
                    ['حران العواميد', 'Harran al-Awamid'],
                    ['المليحة', 'Al-Mleiha'],
                    ['يلدا', 'Yalda'],
                    ['ببيلا', 'Babbila'],
                    ['عربين', 'Arbin'],
                    ['سقبا', 'Saqba'],
                    ['حمورية', 'Hammouriyah'],
                    ['زملكا', 'Zamalka'],
                    ['عين ترما', 'Ain Tarma'],
                    ['كفر بطنا', 'Kafr Batna'],
                    ['كناكر', 'Kanaker'],
                    ['الحرجلة', 'Al-Harjalah'],
                    ['عقربا', 'Aqraba'],
                    ['الناصرية', 'Al-Nasiriyah'],
                    ['عين الفيجة', 'Ain al-Fijeh'],
                    ['جمرايا', 'Jamraya'],
                    ['الهامة', 'Al-Hameh'],
                    ['معربا', 'Maarba'],
                ],
            ],
            [
                'order' => 3,
                'name_ar' => 'حلب',
                'name_en' => 'Aleppo',
                'cities' => [
                    ['حلب', 'Aleppo'],
                    ['عفرين', 'Afrin'],
                    ['عين العرب', 'Ain al-Arab'],
                    ['أعزاز', 'Azaz'],
                    ['الباب', 'Al-Bab'],
                    ['منبج', 'Manbij'],
                    ['جرابلس', 'Jarablus'],
                    ['السفيرة', 'Al-Safira'],
                    ['الاتارب', 'Atarib'],
                    ['دير حافر', 'Deir Hafer'],
                    ['خناصر', 'Khanasser'],
                    ['مسكنة', 'Maskanah'],
                    ['مارع', 'Marea'],
                    ['تل رفعت', 'Tal Rifaat'],
                    ['نبل', 'Nubl'],
                    ['الزهراء', 'Al-Zahraa'],
                    ['عندان', 'Andan'],
                    ['حريتان', 'Haritan'],
                    ['تل عرن', 'Tal Aran'],
                    ['تادف', 'Tadef'],
                    ['أخترين', 'Akhtarin'],
                    ['الراعي', 'Al-Rai'],
                    ['دابق', 'Dabiq'],
                    ['صرين', 'Sarrin'],
                    ['سوران', 'Souran'],
                    ['الجديدة', 'Al-Jadidah'],
                    ['دارتيزة', 'Dar Taizzah'],
                    ['قبتان الجبل', 'Qabtan al-Jabal'],
                    ['الأشرفية', 'Al-Ashrafiyah'],
                    ['الشيخ مقصود', 'Sheikh Maqsoud'],
                    ['صلاح الدين', 'Salah al-Din'],
                    ['السريان الجديدة', 'New Al-Suryan'],
                    ['العزيزية', 'Al-Aziziyah'],
                    ['الفردوس', 'Al-Firdous'],
                    ['السكري', 'Al-Sukkari'],
                    ['الشعار', 'Al-Shaar'],
                    ['الحمدانية', 'Al-Hamdaniyah'],
                    ['بستان القصر', 'Bustan al-Qasr'],
                    ['الجميلية', 'Al-Jamiliyah'],
                    ['الإزة', 'Al-Izaa'],
                    ['الميدان (حلب)', 'Al-Midan (Aleppo)'],
                    ['باب النصر', 'Bab al-Nasr'],
                    ['باب الفرج', 'Bab al-Faraj'],
                ],
            ],
            [
                'order' => 4,
                'name_ar' => 'حمص',
                'name_en' => 'Homs',
                'cities' => [
                    ['حمص', 'Homs'],
                    ['تدمر', 'Palmyra'],
                    ['الرستن', 'Al-Rastan'],
                    ['تلبيسة', 'Talbiseh'],
                    ['القصير', 'Al-Qusayr'],
                    ['تلكلخ', 'Tal Kalakh'],
                    ['تلدو', 'Taldou'],
                    ['كفرلاها', 'Kafr Laha'],
                    ['المخرم', 'Al-Makhram'],
                    ['السخنة', 'Al-Sukhnah'],
                    ['القريتين', 'Al-Qaryatayn'],
                    ['مهين', 'Mahin'],
                    ['صدد', 'Sadad'],
                    ['حسياء', 'Hassia'],
                    ['الفرقلس', 'Al-Furqlus'],
                    ['عين النسر', 'Ain al-Nasr'],
                    ['المشرفة', 'Al-Mashrafah'],
                    ['الدار الكبيرة', 'Al-Dar al-Kabirah'],
                    ['كفر عايا', 'Kafr Aya'],
                    ['فيروزة', 'Fairouzeh'],
                    ['الخالدية', 'Al-Khalidiyah'],
                    ['الغنطة', 'Al-Ghantah'],
                    ['غزالة', 'Ghazaleh'],
                    ['مرمريتا', 'Marmarita'],
                    ['مشتى عازار', 'Mashta Azar'],
                    ['رباح', 'Rabah'],
                    ['الحصن', 'Al-Husn'],
                    ['كفرام', 'Kafram'],
                    ['الحواش', 'Al-Hawash'],
                ],
            ],
            [
                'order' => 5,
                'name_ar' => 'حماة',
                'name_en' => 'Hama',
                'cities' => [
                    ['حماة', 'Hama'],
                    ['سلمية', 'Salamiyah'],
                    ['مصياف', 'Masyaf'],
                    ['محردة', 'Mhardeh'],
                    ['السقيلبية', 'Al-Suqaylabiyah'],
                    ['صوران', 'Souran'],
                    ['طيبة الإمام', 'Taybat al-Imam'],
                    ['اللطامنة', 'Al-Latamneh'],
                    ['حلفايا', 'Halfaya'],
                    ['كفرزيتا', 'Kafr Zita'],
                    ['سلحب', 'Salhab'],
                    ['السليمة', 'Al-Salimah'],
                    ['سحلب', 'Sahlab'],
                    ['مورك', 'Morek'],
                    ['كرناز', 'Karnaz'],
                    ['كفرنبودة', 'Kafr Nabudah'],
                    ['قلعة المضيق', 'Qalaat al-Madiq'],
                    ['شيزر', 'Shaizar'],
                    ['جب رملة', 'Jubb Ramlah'],
                    ['عين حلاقيم', 'Ain Halaqim'],
                    ['وادي العيون', 'Wadi al-Uyun'],
                    ['الحمراء', 'Al-Hamra'],
                    ['صبورة', 'Saburah'],
                    ['قمحانة', 'Qamhanah'],
                    ['بري الشرقي', 'Barri al-Sharqi'],
                    ['السعن', 'Al-Saan'],
                    ['عقربات', 'Aqrabat'],
                    ['معردس', 'Maardas'],
                    ['سريحين', 'Surayhin'],
                    ['المحروسة', 'Al-Mahrousah'],
                    ['جورين', 'Jurin'],
                ],
            ],
            [
                'order' => 6,
                'name_ar' => 'اللاذقية',
                'name_en' => 'Latakia',
                'cities' => [
                    ['اللاذقية', 'Latakia'],
                    ['جبلة', 'Jableh'],
                    ['الحفة', 'Al-Haffah'],
                    ['القرداحة', 'Al-Qardahah'],
                    ['كسب', 'Kessab'],
                    ['صلنفة', 'Slunfeh'],
                    ['كنسبا', 'Kinsabba'],
                    ['سلمى', 'Salma'],
                    ['المزيرعة', 'Al-Muzayraah'],
                    ['عين التينة', 'Ain al-Tineh'],
                    ['ربيعة', 'Rabiah'],
                    ['البهلولية', 'Al-Bahluliyah'],
                    ['عين البيضا', 'Ain al-Bayda'],
                    ['قسطل معاف', 'Qastal Maaf'],
                    ['بيت ياشوط', 'Beit Yashout'],
                    ['عين الشرقية', 'Ain al-Sharqiyah'],
                    ['القطيلبية', 'Al-Qutaylibiyah'],
                    ['الدالية', 'Al-Daliyah'],
                    ['عين شقاق', 'Ain Shaqaq'],
                    ['هنادي', 'Hanadi'],
                    ['حرف المسيترة', 'Harf al-Musaytirah'],
                    ['الفاخورة', 'Al-Fakhourah'],
                    ['بستان الباشا', 'Bustan al-Basha'],
                    ['جوبة برغال', 'Jobat Burghal'],
                    ['طوما', 'Touma'],
                ],
            ],
            [
                'order' => 7,
                'name_ar' => 'طرطوس',
                'name_en' => 'Tartus',
                'cities' => [
                    ['طرطوس', 'Tartus'],
                    ['بانياس', 'Baniyas'],
                    ['صافيتا', 'Safita'],
                    ['دريكيش', 'Dreikish'],
                    ['الشيخ بدر', 'Sheikh Badr'],
                    ['القدموس', 'Al-Qadmus'],
                    ['حمام واصل', 'Hammam Wasel'],
                    ['مشتى الحلو', 'Mashta al-Helu'],
                    ['الكفرون', 'Al-Kafroun'],
                    ['أرواد', 'Arwad'],
                    ['الحميدية', 'Al-Hamidiyah'],
                    ['حمين', 'Hamin'],
                    ['خربة المعزة', 'Khirbet al-Maazah'],
                    ['دوير رسلان', 'Duwayr Raslan'],
                    ['رأس الخشوفة', 'Ras al-Khashufah'],
                    ['الروضة', 'Al-Rawdah'],
                    ['سبة', 'Sibbeh'],
                    ['السودا', 'Al-Sawda'],
                    ['السيسنية', 'Al-Saysaniyah'],
                    ['صفصافة', 'Safsafah'],
                    ['الطواحين', 'Al-Tawahin'],
                    ['العنازة', 'Al-Anazah'],
                    ['القمصية', 'Al-Qumsiyah'],
                    ['الكريمة', 'Al-Karimah'],
                    ['البارقية', 'Al-Bariqiyah'],
                    ['برمانة المشايخ', 'Barmanet al-Mashayekh'],
                    ['تالين', 'Talin'],
                    ['جنينة رسلان', 'Junaynat Raslan'],
                ],
            ],
            [
                'order' => 8,
                'name_ar' => 'إدلب',
                'name_en' => 'Idlib',
                'cities' => [
                    ['إدلب', 'Idlib'],
                    ['أريحا', 'Ariha'],
                    ['معرة النعمان', 'Maarat al-Numan'],
                    ['جسر الشغور', 'Jisr al-Shughur'],
                    ['حارم', 'Harem'],
                    ['خان شيخون', 'Khan Shaykhun'],
                    ['سراقب', 'Saraqib'],
                    ['بنش', 'Binnish'],
                    ['معرة مصرين', 'Maarat Misrin'],
                    ['الدانا', 'Al-Dana'],
                    ['أبو الظهور', 'Abu al-Duhur'],
                    ['سلقين', 'Salqin'],
                    ['كفرنبل', 'Kafr Nabl'],
                    ['سرمين', 'Sarmin'],
                    ['أرمناز', 'Armanaz'],
                    ['كفر تخاريم', 'Kafr Takharim'],
                    ['الفوعة', 'Al-Fuah'],
                    ['تفتناز', 'Taftanaz'],
                    ['حيش', 'Heish'],
                    ['الجانودية', 'Al-Janudiyah'],
                    ['التمانعة', 'Al-Tamanah'],
                    ['سنجار', 'Sinjar'],
                    ['قورقنيا', 'Qourqeena'],
                    ['إحسم', 'Ehsem'],
                    ['بداما', 'Badama'],
                    ['دركوش', 'Darkoush'],
                    ['محمبل', 'Muhambal'],
                    ['سرمدا', 'Sarmada'],
                    ['كللي', 'Kelly'],
                    ['أورم الكبرى', 'Urum al-Kubra'],
                    ['كورين', 'Kourin'],
                    ['تلمنس', 'Talmenes'],
                ],
            ],
            [
                'order' => 9,
                'name_ar' => 'دير الزور',
                'name_en' => 'Deir ez-Zor',
                'cities' => [
                    ['دير الزور', 'Deir ez-Zor'],
                    ['الميادين', 'Al-Mayadin'],
                    ['البوكمال', 'Albu Kamal'],
                    ['الصور', 'Al-Sour'],
                    ['موحسن', 'Muhasan'],
                    ['العشارة', 'Al-Ashara'],
                    ['التبني', 'Al-Tibni'],
                    ['الجلاء', 'Al-Jalaa'],
                    ['ذيبان', 'Dhiban'],
                    ['سوسة', 'Sousah'],
                    ['هجين', 'Hajin'],
                    ['البصيرة', 'Al-Basirah'],
                    ['خشام', 'Khasham'],
                    ['كسرة', 'Kasrah'],
                    ['الشحيل', 'Al-Shuhayl'],
                    ['صبيخان', 'Subaykhan'],
                    ['محكان', 'Mahkan'],
                    ['الشعفة', 'Al-Shaafah'],
                    ['الكشكية', 'Al-Kishkiyah'],
                    ['طابية جزيرة', 'Tabiyat Jazirah'],
                ],
            ],
            [
                'order' => 10,
                'name_ar' => 'الرقة',
                'name_en' => 'Raqqa',
                'cities' => [
                    ['الرقة', 'Raqqa'],
                    ['الثورة', 'Al-Thawrah'],
                    ['تل أبيض', 'Tal Abyad'],
                    ['عين عيسى', 'Ain Issa'],
                    ['سلوك', 'Suluk'],
                    ['معدان', 'Maadan'],
                    ['المنصورة', 'Al-Mansurah'],
                    ['الكرامة', 'Al-Karamah'],
                    ['الجرنية', 'Al-Jarniyah'],
                    ['السبخة', 'Al-Sabkhah'],
                    ['الناصرية', 'Al-Nasiriyah'],
                    ['المحمودلي', 'Al-Mahmudli'],
                    ['تل السمن', 'Tal al-Samn'],
                ],
            ],
            [
                'order' => 11,
                'name_ar' => 'الحسكة',
                'name_en' => 'Al-Hasakah',
                'cities' => [
                    ['الحسكة', 'Al-Hasakah'],
                    ['القامشلي', 'Qamishli'],
                    ['المالكية', 'Al-Malikiyah'],
                    ['رأس العين', 'Ras al-Ayn'],
                    ['الشدادي', 'Al-Shaddadi'],
                    ['عامودا', 'Amuda'],
                    ['الدرباسية', 'Al-Darbasiyah'],
                    ['الهول', 'Al-Hawl'],
                    ['القحطانية', 'Al-Qahtaniyah'],
                    ['اليعربية', 'Al-Yarubiyah'],
                    ['تل تمر', 'Tal Tamr'],
                    ['تل براك', 'Tal Brak'],
                    ['تل حميس', 'Tal Hamis'],
                    ['مركدة', 'Markada'],
                    ['العريشة', 'Al-Arishah'],
                    ['أبو رأسين', 'Abu Rasin'],
                    ['بئر الحلو الوردية', 'Bir al-Helu al-Wardiyah'],
                    ['المعبدة', 'Al-Maabadah'],
                    ['اليرموك (جزعة)', 'Al-Yarmouk (Jazaa)'],
                    ['الجبسة', 'Al-Jibsah'],
                    ['المناجير', 'Al-Managir'],
                    ['السبعة وأربعين', 'Al-Sabaa wa Arbain'],
                ],
            ],
            [
                'order' => 12,
                'name_ar' => 'درعا',
                'name_en' => 'Daraa',
                'cities' => [
                    ['درعا', 'Daraa'],
                    ['الصنمين', 'Al-Sanamayn'],
                    ['إزرع', 'Izraa'],
                    ['نوى', 'Nawa'],
                    ['بصرى الشام', 'Bosra al-Sham'],
                    ['طفس', 'Tafas'],
                    ['جاسم', 'Jasim'],
                    ['الشيخ مسكين', 'Al-Sheikh Maskin'],
                    ['داعل', 'Dael'],
                    ['الحراك', 'Al-Harak'],
                    ['خربة غزالة', 'Khirbet Ghazaleh'],
                    ['المزيريب', 'Al-Muzayrib'],
                    ['غباغب', 'Ghabaghib'],
                    ['تسيل', 'Tasil'],
                    ['الشجرة', 'Al-Shajarah'],
                    ['الجيزة', 'Al-Jizah'],
                    ['المسمية', 'Al-Musaymiah'],
                    ['المسيفرة', 'Al-Musayfirah'],
                    ['نمر', 'Namir'],
                    ['تل شهاب', 'Tal Shihab'],
                    ['صيدا', 'Saida'],
                    ['خبب', 'Khabab'],
                    ['كفر شمس', 'Kafr Shams'],
                    ['الطيبة', 'Al-Taybah'],
                    ['جمرين', 'Jamrin'],
                    ['عتمان', 'Atman'],
                    ['اليادودة', 'Al-Yadudah'],
                    ['أم ولد', 'Umm Walad'],
                    ['الكرك الشرقي', 'Al-Karak al-Sharqi'],
                    ['بصير', 'Busayr'],
                ],
            ],
            [
                'order' => 13,
                'name_ar' => 'السويداء',
                'name_en' => 'As-Suwayda',
                'cities' => [
                    ['السويداء', 'As-Suwayda'],
                    ['شهبا', 'Shahba'],
                    ['صلخد', 'Salkhad'],
                    ['القريا', 'Al-Qurayya'],
                    ['ملح', 'Malah'],
                    ['عريقة', 'Ariqah'],
                    ['ذيبين', 'Dhibin'],
                    ['المشنف', 'Al-Mushannaf'],
                    ['المزرعة', 'Al-Mazraah'],
                    ['الغارية', 'Al-Ghariyah'],
                    ['شقا', 'Shaqqa'],
                    ['عتيل', 'Atil'],
                    ['الكفر', 'Al-Kafr'],
                    ['عرى', 'Ara'],
                ],
            ],
            [
                'order' => 14,
                'name_ar' => 'القنيطرة',
                'name_en' => 'Quneitra',
                'cities' => [
                    ['القنيطرة', 'Quneitra'],
                    ['خان أرنبة', 'Khan Arnaba'],
                    ['مدينة السلام', 'Madinat al-Salam'],
                    ['فيق', 'Fiq'],
                    ['البطيحة', 'Al-Butayhah'],
                    ['الخشنية', 'Al-Khushniyah'],
                    ['مسعدة', 'Masaadeh'],
                    ['مجدل شمس', 'Majdal Shams'],
                    ['بقعاثا', 'Buqatha'],
                    ['عين قنية', 'Ain Qiniyeh'],
                    ['بئر عجم', 'Bir Ajam'],
                    ['الرفيد', 'Al-Rafid'],
                    ['غجر', 'Ghajar'],
                    ['جباتا الخشب', 'Jubbata al-Khashab'],
                    ['حضر', 'Hadar'],
                    ['جبا', 'Jaba'],
                    ['مسحرة', 'Mashara'],
                ],
            ],
        ];
    }
}
