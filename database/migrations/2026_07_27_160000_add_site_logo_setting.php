<?php

use App\Models\Settings\Setting;
use Illuminate\Database\Migrations\Migration;

class AddSiteLogoSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Setting::where('key', 'site.logo')->exists()) {
            Setting::create([
                'name' => 'Site logo',
                'key' => 'site.logo',
                'type' => 'general',
                'value' => 'assets/images/logo_light.png',
                'value_type' => 'string',
                'description' => 'This is for Site logo shown in admin and frontend.',
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
        Setting::where('key', 'site.logo')->delete();
    }
}
