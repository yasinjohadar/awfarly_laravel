<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertiserPreferredLocationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertiser_preferred_governorates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->unsignedBigInteger('governorate_id');
            $table->timestamps();

            $table->unique(['advertiser_id', 'governorate_id'], 'adv_pref_gov_unique');

            $table->foreign('advertiser_id')
                ->on('advertisers_users')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('governorate_id')
                ->on('governorates')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('advertiser_preferred_cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->unsignedBigInteger('city_id');
            $table->timestamps();

            $table->unique(['advertiser_id', 'city_id'], 'adv_pref_city_unique');

            $table->foreign('advertiser_id')
                ->on('advertisers_users')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('city_id')
                ->on('cities')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertiser_preferred_cities');
        Schema::dropIfExists('advertiser_preferred_governorates');
    }
}
