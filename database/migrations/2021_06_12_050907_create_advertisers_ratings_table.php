<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisersRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisers_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->longText('comment')->nullable();
            $table->decimal('rate')->default(0.00);
            $table->enum('status', [
                'approved',
                'pending',
                'unapproved',
            ]);
            $table->timestamps();

            $table->foreign('advertiser_id')
                ->references('id')->on('advertisers_users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisers_ratings');
    }
}
