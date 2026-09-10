<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedWhatsappMessageTemplates extends Migration
{
    /**
     * purpose key => [name, default content]
     */
    protected function templates(): array
    {
        return [
            'register_otp' => [
                'name' => 'Registration OTP',
                'content' => "مرحبًا بك في {site}!\nرمز التحقق الخاص بك لإكمال التسجيل هو: {code}\nصالح لمدة {minutes} دقيقة.",
            ],
            'login_otp' => [
                'name' => 'Login OTP',
                'content' => "رمز تسجيل الدخول الخاص بك في {site} هو: {code}\nصالح لمدة {minutes} دقيقة. لا تشاركه مع أحد.",
            ],
            'password_reset_otp' => [
                'name' => 'Password Reset OTP',
                'content' => "رمز استعادة كلمة المرور في {site} هو: {code}\nصالح لمدة {minutes} دقيقة. إذا لم تطلب ذلك يمكنك تجاهل هذه الرسالة.",
            ],
        ];
    }

    public function up()
    {
        foreach ($this->templates() as $purpose => $template) {
            $settingKey = "whatsapp.templates.$purpose";

            if (DB::table('settings')->where('key', $settingKey)->exists()) {
                continue;
            }

            $templateId = DB::table('whatsapp_message_templates')->insertGetId([
                'name' => $template['name'],
                'content' => $template['content'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('settings')->insert([
                'name' => 'WhatsApp Template — ' . $template['name'],
                'key' => $settingKey,
                'type' => 'whatsapp',
                'value' => (string) $templateId,
                'value_type' => 'integer',
                'description' => 'Id of the whatsapp_message_templates row used for this purpose.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        foreach (array_keys($this->templates()) as $purpose) {
            DB::table('settings')->where('key', "whatsapp.templates.$purpose")->delete();
        }
    }
}
