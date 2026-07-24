<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('name_en');
            $table->string('name_ar');
            $table->unsignedInteger('maximum_posts');
            $table->longText('description_en')->nullable();
            $table->longText('description_ar')->nullable();
            $table->json('specifications_en')->nullable();
            $table->json('specifications_ar')->nullable();
            $table->decimal('price');
            $table->decimal('old_price')->nullable();
            $table->string('subscription_type')->default('monthly');
            $table->integer('duration');
            $table->enum('currency', [
                'SAR',
                'USD'
            ])->default('SAR');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial')->default(false);
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
        Schema::dropIfExists('packages');
    }
}
