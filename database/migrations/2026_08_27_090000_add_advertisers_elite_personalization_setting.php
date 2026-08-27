<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdvertisersElitePersonalizationSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('settings')->where('key', 'advertisers.elite.personalize')->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'name' => 'Personalize Elite Advertisers',
                'key' => 'advertisers.elite.personalize',
                'type' => 'users',
                // default true: preserves today's behavior (interest/location personalization
                // already runs unconditionally for logged-in viewers) until an admin turns it off.
                'value' => '1',
                'value_type' => 'boolean',
                'description' => 'When enabled, elite advertisers are filtered by the viewer\'s followed interests and preferred governorate/city. When disabled, all elite advertisers are shown generally.',
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
        DB::table('settings')->where('key', 'advertisers.elite.personalize')->delete();
    }
}
