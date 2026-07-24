<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'any', 'website', 'mobile',
            ])->default('any');
            $table->enum('users', [
                'any', 'customers', 'advertisers',
            ])->default('any');
            $table->string('advertiser_name')->nullable();
            $table->string('advertiser_url')->nullable();
            $table->string('advertiser_image')->nullable();
            $table->longText('content')->nullable();
            $table->json('categories')->nullable();
            $table->json('countries')->nullable();
            $table->json('cities')->nullable();
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisements');
    }
}
