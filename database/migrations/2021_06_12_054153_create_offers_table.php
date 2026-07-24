<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('advertiser_id');
            $table->longText('content')->nullable();
            $table->decimal('sale_percentage')->default(0);
            $table->string('advertisement_url')->nullable();
            $table->decimal('rate')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->integer('expires_in')->nullable();
            $table->enum('status', [
                'approved',
                'pending',
                'unapproved',
            ]);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('advertiser_id')
                ->references('id')->on('advertisers_users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('category_id')
                ->on('categories')->references('id')
                ->nullOnDelete()
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
        Schema::dropIfExists('offers');
    }
}
