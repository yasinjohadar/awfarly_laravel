<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageFieldToCustomersUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers_users', function (Blueprint $table) {
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
        if (Schema::hasColumn('customers_users', 'notify_language')) {
            Schema::table('customers_users', function (Blueprint $table) {
                $table->dropColumn('notify_language');
            });
        }
    }
}
