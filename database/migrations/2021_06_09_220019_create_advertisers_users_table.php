<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisersUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisers_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('business_type');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->longText('bio')->nullable();
            $table->string('image')->nullable();
            $table->string('country_code')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('language_code')->default('ar');
            $table->string('fcm_token')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('website_url')->nullable();
            $table->unsignedInteger('allowed_posts_count')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->decimal('rate')->nullable();
            $table->enum('status', [
                'active',
                'inactive',
                'banned',
            ])->default('active');
            $table->boolean('is_elite')->default(false);
            $table->boolean('is_follow_allowed')->default(true);
            $table->boolean('is_accepted_send_notifications')->default(false);
            $table->double('address_latitude')->nullable();
            $table->double('address_longitude')->nullable();
            $table->enum('chats_privacy', [
                'public',
                'followers',
                'disabled',
            ])->default('public');
            $table->enum('profile_privacy', [
                'public',
                'followers',
                'private',
            ])->default('public');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_online_at')->nullable();
            $table->boolean('is_online')->default(false);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('country_code')
                ->references('code')->on('countries')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('city_id')
                ->references('id')->on('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('business_type')
                ->references('id')->on('advertisers_business_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisers_users');
    }
}
