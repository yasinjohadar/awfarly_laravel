<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdvertisersDefaultPackageSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('settings')->where('key', 'advertisers.default_package_id')->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'name' => 'Advertisers Default Package',
                'key' => 'advertisers.default_package_id',
                'type' => 'users',
                'value' => '',
                'value_type' => 'integer',
                'description' => 'Package automatically granted to every newly registered advertiser. Leave empty to disable.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', 'advertisers.default_package_id')->delete();
    }
}
