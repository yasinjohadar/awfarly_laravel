<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameCitiesToGovernoratesAndAddCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });

        Schema::table('customers_users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });

        Schema::rename('cities', 'governorates');

        // Avoid doctrine/dbal dependency on Laravel 8
        DB::statement('ALTER TABLE `advertisers_users` CHANGE `city_id` `governorate_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `customers_users` CHANGE `city_id` `governorate_id` BIGINT UNSIGNED NULL');

        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->foreign('governorate_id')
                ->references('id')->on('governorates')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('customers_users', function (Blueprint $table) {
            $table->foreign('governorate_id')
                ->references('id')->on('governorates')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('order')->nullable();
            $table->unsignedBigInteger('governorate_id');
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();

            $table->unique(['governorate_id', 'name_ar']);
            $table->unique(['governorate_id', 'name_en']);

            $table->foreign('governorate_id')
                ->references('id')->on('governorates')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');
            $table->foreign('city_id')
                ->references('id')->on('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('customers_users', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');
            $table->foreign('city_id')
                ->references('id')->on('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        if (Schema::hasTable('advertisements') && Schema::hasColumn('advertisements', 'cities')) {
            DB::statement('ALTER TABLE `advertisements` CHANGE `cities` `governorates` JSON NULL');
            Schema::table('advertisements', function (Blueprint $table) {
                $table->json('cities')->nullable()->after('governorates');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('advertisements') && Schema::hasColumn('advertisements', 'governorates')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropColumn('cities');
            });
            DB::statement('ALTER TABLE `advertisements` CHANGE `governorates` `cities` JSON NULL');
        }

        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
            $table->dropForeign(['governorate_id']);
        });

        Schema::table('customers_users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
            $table->dropForeign(['governorate_id']);
        });

        Schema::dropIfExists('cities');

        DB::statement('ALTER TABLE `advertisers_users` CHANGE `governorate_id` `city_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `customers_users` CHANGE `governorate_id` `city_id` BIGINT UNSIGNED NULL');

        Schema::rename('governorates', 'cities');

        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->foreign('city_id')
                ->references('id')->on('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('customers_users', function (Blueprint $table) {
            $table->foreign('city_id')
                ->references('id')->on('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
}
