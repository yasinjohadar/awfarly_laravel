<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddHasCategoriesToAdvertisersBusinessTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisers_business_types', function (Blueprint $table) {
            $table->boolean('has_categories')->default(true)->after('is_active');
        });

        // "Shopper" advertisers deal across every category rather than a
        // specialization, so they're exempt from the advertiser-level
        // category match in CategoriesFilter::applyFeedAdvertiserCategoryFilter().
        DB::table('advertisers_business_types')->updateOrInsert(
            ['name_en' => 'Shopper'],
            [
                'name_ar' => 'متسوق',
                'is_active' => true,
                'has_categories' => false,
                'order' => DB::table('advertisers_business_types')->max('order') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('advertisers_business_types')->where('name_en', 'Shopper')->delete();

        Schema::table('advertisers_business_types', function (Blueprint $table) {
            $table->dropColumn('has_categories');
        });
    }
}
