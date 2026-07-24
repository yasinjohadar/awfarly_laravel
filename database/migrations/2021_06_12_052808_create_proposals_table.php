<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('advertiser_id');
            $table->longText('content');
            $table->longText('answer')->nullable();
            $table->integer('expires_in')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('advertiser_id')
                ->on('advertisers_users')->references('id')
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
        Schema::dropIfExists('proposals');
    }
}
