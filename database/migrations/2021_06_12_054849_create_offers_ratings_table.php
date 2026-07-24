<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
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

            $table->foreign('offer_id')
                ->on('offers')->references('id')
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
        Schema::dropIfExists('offers_ratings');
    }
}
