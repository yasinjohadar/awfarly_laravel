<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMonthlyOffersLimitsToPackagesAndAdvertisers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedInteger('maximum_monthly_offers')->default(30)->after('maximum_offers');
        });

        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->unsignedInteger('maximum_monthly_offers')->nullable()->after('allowed_offers_count');
        });

        $exists = DB::table('settings')->where('key', 'max.advertiser.monthly.offers')->exists();
        if (!$exists) {
            DB::table('settings')->insert([
                'name' => 'Maximum Advertiser Monthly Offers',
                'key' => 'max.advertiser.monthly.offers',
                'value' => '30',
                'value_type' => 'integer',
                'type' => 'offers',
                'description' => 'Maximum number of offers an advertiser can create per calendar month.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('maximum_monthly_offers');
        });

        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->dropColumn('maximum_monthly_offers');
        });

        DB::table('settings')->where('key', 'max.advertiser.monthly.offers')->delete();
    }
}
