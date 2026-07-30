<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerPreferredLocationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_preferred_governorates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('governorate_id');
            $table->timestamps();

            $table->unique(['customer_id', 'governorate_id'], 'cust_pref_gov_unique');

            $table->foreign('customer_id')
                ->on('customers_users')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('governorate_id')
                ->on('governorates')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('customer_preferred_cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('city_id');
            $table->timestamps();

            $table->unique(['customer_id', 'city_id'], 'cust_pref_city_unique');

            $table->foreign('customer_id')
                ->on('customers_users')->references('id')
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
        Schema::dropIfExists('customer_preferred_cities');
        Schema::dropIfExists('customer_preferred_governorates');
    }
}
