<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPostsDefaultAutoApproveSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('settings')->where('key', 'posts.default.auto.approve')->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'name' => 'Posts Add Auto Approve',
                'key' => 'posts.default.auto.approve',
                'type' => 'posts',
                'value' => '0',
                'value_type' => 'boolean',
                'description' => 'Selects whether to auto approve adding a post or not.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('settings')->where('key', 'posts.default.auto.approve')->delete();
    }
}
