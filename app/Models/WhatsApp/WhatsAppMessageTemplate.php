<?php

namespace App\Models\WhatsApp;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessageTemplate extends Model
{
    protected $table = 'whatsapp_message_templates';

    protected $fillable = [
        'name',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Placeholders every template may use, and where the value comes from at send time.
     */
    public static function availablePlaceholders(): array
    {
        return [
            'code' => 'رمز التحقق (OTP)',
            'site' => 'اسم الموقع',
            'minutes' => 'مدة صلاحية الرمز بالدقائق',
        ];
    }

    /**
     * Render this template's content, substituting {placeholder} tokens.
     *
     * @param array<string,string> $vars
     */
    public function render(array $vars): string
    {
        $replacements = [];
        foreach ($vars as $key => $value) {
            $replacements['{' . $key . '}'] = $value;
        }

        return strtr($this->content, $replacements);
    }

    /**
     * The template currently assigned to the given purpose (e.g. 'register_otp'),
     * or null if none is configured / the assigned row no longer exists.
     */
    public static function forPurpose(string $purpose): ?self
    {
        $id = Settings::Get("whatsapp.templates.$purpose");

        if (!$id) {
            return null;
        }

        return static::find($id);
    }
}
