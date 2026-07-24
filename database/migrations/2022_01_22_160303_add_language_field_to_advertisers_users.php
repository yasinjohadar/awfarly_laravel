<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageFieldToAdvertisersUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->enum('notify_language', ['ar', 'en'])->default('ar')->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('advertisers_users', 'notify_language')) {
            Schema::table('advertisers_users', function (Blueprint $table) {
                $table->dropColumn('notify_language');
            });
        }
    }
}
