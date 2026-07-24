<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersFollowingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_followings', function (Blueprint $table) {
            $table->id();
            $table->string('followed_type');
            $table->unsignedBigInteger('followed_id');
            $table->string('follower_type');
            $table->unsignedBigInteger('follower_id');
            $table->enum('status', [
                'pending',
                'approved',
                'declined'
            ])->default('pending');
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
        Schema::dropIfExists('users_followings');
    }
}
