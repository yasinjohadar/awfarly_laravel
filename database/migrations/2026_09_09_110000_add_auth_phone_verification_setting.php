<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAuthPhoneVerificationSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('settings')->where('key', 'auth.phone_verification.enabled')->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'name' => 'Phone Verification Enabled',
                'key' => 'auth.phone_verification.enabled',
                'type' => 'users',
                'value' => '0',
                'value_type' => 'boolean',
                'description' => 'Requires a WhatsApp OTP code to verify the mobile number before a new account is created.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('settings')->where('key', 'auth.phone_verification.enabled')->delete();
    }
}
