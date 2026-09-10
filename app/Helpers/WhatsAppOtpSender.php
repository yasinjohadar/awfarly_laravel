<?php

namespace App\Helpers;

use App\Models\WhatsApp\WhatsAppMessageTemplate;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppOtpSender
{
    /**
     * Minutes an OTP code stays valid for — kept in sync with
     * ActivationCodeService::generate()'s hardcoded 30-minute cache TTL.
     */
    public const CODE_TTL_MINUTES = 30;

    /**
     * Send an OTP code to the given mobile number over WhatsApp.
     *
     * The message text comes from the admin-managed template assigned to
     * $purpose (see the "WhatsApp > Templates" admin page); if none is
     * configured, a built-in default message is used instead.
     *
     * @param string $mobile
     * @param string $code
     * @param string $purpose one of 'register_otp', 'login_otp', 'password_reset_otp'
     * @return bool
     */
    public static function send(string $mobile, string $code, string $purpose = 'register_otp'): bool
    {
        $message = static::renderMessage($code, $purpose);

        try {
            $result = app(EvolutionRotatingSendService::class)->sendWithRotation(
                function (string $instanceName) use ($mobile, $message) {
                    return app(EvolutionService::class)->clientFor(null, $instanceName)->sendText($instanceName, $mobile, $message);
                }
            );

            return (bool) $result;
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('OTP send failed: ' . $e->getMessage(), ['mobile' => $mobile]);
            return false;
        }
    }

    /**
     * Render the message text for a given purpose/code, using the admin-assigned
     * template when one exists and is active, falling back to a built-in default.
     */
    public static function renderMessage(string $code, string $purpose = 'register_otp'): string
    {
        $siteName = Settings::Get('site.name', config('app.name'));
        $vars = [
            'code' => $code,
            'site' => $siteName,
            'minutes' => (string) static::CODE_TTL_MINUTES,
        ];

        $template = WhatsAppMessageTemplate::forPurpose($purpose);
        if ($template && $template->is_active) {
            return $template->render($vars);
        }

        // No template configured — fall back to the original hardcoded message.
        return trans('api/notifications/whatsapp_otp.message', [
            'code' => $code,
            'site' => $siteName,
        ], 'ar');
    }
}
