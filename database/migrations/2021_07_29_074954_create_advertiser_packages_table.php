<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertiserPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertiser_packages', function (Blueprint $table) {
            $table->id();
            $table->string('unique_identifier')->nullable()->unique();
            $table->longText('receipt_data')->nullable();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('advertiser_id');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->integer('purchase_count')->default(1);
            $table->boolean('is_ended')->default(false);
            $table->boolean('is_current')->default(true);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('package_id')
                ->on('packages')->references('id')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('advertiser_id')
                ->on('advertisers_users')->references('id')
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
        Schema::dropIfExists('advertiser_packages');
    }
}
