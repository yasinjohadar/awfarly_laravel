<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFirebaseCredentialsFileSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('settings')->where('key', 'firebase.credentials.file')->exists();

        if (!$exists) {
            //default: the same file already referenced by .env's FIREBASE_CREDENTIALS,
            //resolved to an absolute path so the admin UI's upload flow (which also
            //stores absolute paths) and the pre-existing env-based setup behave identically.
            $default = env('FIREBASE_CREDENTIALS')
                ? base_path(env('FIREBASE_CREDENTIALS'))
                : '';

            DB::table('settings')->insert([
                'name' => 'Firebase Credentials File',
                'key' => 'firebase.credentials.file',
                'type' => 'general',
                'value' => $default,
                'value_type' => 'string',
                'description' => 'Absolute path to the Firebase service-account JSON file used for push notifications and phone-number login. Managed from the dedicated Firebase settings page, not the generic settings editor.',
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
        DB::table('settings')->where('key', 'firebase.credentials.file')->delete();
    }
}
