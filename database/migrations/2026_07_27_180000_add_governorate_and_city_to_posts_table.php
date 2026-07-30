<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGovernorateAndCityToPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');

            $table->foreign('governorate_id')
                ->references('id')
                ->on('governorates')
                ->restrictOnDelete();

            $table->foreign('city_id')
                ->references('id')
                ->on('cities')
                ->restrictOnDelete();
        });

        // Backfill from advertiser author location when available
        if (Schema::hasTable('advertisers_users') && Schema::hasColumn('advertisers_users', 'governorate_id')) {
            DB::statement("
                UPDATE posts
                INNER JOIN advertisers_users ON advertisers_users.id = posts.user_id
                    AND posts.user_type = 'App\\\\Models\\\\Users\\\\Advertisers\\\\AdvertiserUser'
                SET posts.governorate_id = advertisers_users.governorate_id,
                    posts.city_id = advertisers_users.city_id
                WHERE posts.governorate_id IS NULL
            ");
        }
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['governorate_id', 'city_id']);
        });
    }
}
