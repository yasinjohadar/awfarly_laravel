<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAutoDeleteAfterDaysSettings extends Migration
{
    public function up()
    {
        $now = now();

        $settings = [
            [
                'name' => 'Posts Auto Delete After Days',
                'key' => 'posts.auto_delete_after_days',
                'value' => '0',
                'value_type' => 'integer',
                'type' => 'posts',
                'description' => 'Number of days after publish to permanently delete posts. 0 disables auto-delete.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Offers Auto Delete After Days',
                'key' => 'offers.auto_delete_after_days',
                'value' => '0',
                'value_type' => 'integer',
                'type' => 'offers',
                'description' => 'Number of days after publish to permanently delete offers. 0 disables auto-delete.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    public function down()
    {
        DB::table('settings')
            ->whereIn('key', [
                'posts.auto_delete_after_days',
                'offers.auto_delete_after_days',
            ])
            ->delete();
    }
}
