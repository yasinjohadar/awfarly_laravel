<?php

namespace App\Helpers;

use App\Models\WhatsApp\EvolutionInstance;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends a WhatsApp alert to the admin whenever users add content the admin should
 * know about (offers, posts, comments, reports, contact-us messages).
 *
 * The target is the `whatsapp.admin_notifications.phone` setting, falling back to
 * the phone number of the system's default (primary) WhatsApp instance.
 */
class AdminWhatsAppNotifier
{
    public static function isEnabled(): bool
    {
        return (bool) Settings::Get('whatsapp.admin_notifications.enabled', true);
    }

    public static function targetPhone(): string
    {
        $phone = trim((string) Settings::Get('whatsapp.admin_notifications.phone', ''));
        if ($phone !== '') {
            return $phone;
        }

        return (string) (EvolutionInstance::defaultInstance()?->phone_number ?? '');
    }

    /**
     * Queue the alert to be sent after the HTTP response is flushed, so the user who
     * triggered it never waits on (or fails because of) the WhatsApp API.
     *
     * @param callable(): ?string $buildMessage returns null to skip (e.g. the row was rolled back)
     */
    public static function notifyLater(callable $buildMessage): void
    {
        // Seeders, migrations and artisan commands must not spam the admin.
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        app()->terminating(function () use ($buildMessage) {
            try {
                $message = $buildMessage();
                if ($message !== null) {
                    static::send($message);
                }
            } catch (Throwable $e) {
                Log::channel('whatsapp')->error('Admin WhatsApp notification failed: ' . $e->getMessage());
            }
        });
    }

    public static function send(string $message): bool
    {
        if (!static::isEnabled()) {
            return false;
        }

        $phone = static::targetPhone();
        if ($phone === '') {
            Log::channel('whatsapp')->warning('Admin WhatsApp notification skipped: no admin phone configured.');
            return false;
        }

        try {
            $result = app(EvolutionRotatingSendService::class)->sendWithRotation(
                function (string $instanceName) use ($phone, $message) {
                    return app(EvolutionService::class)->clientFor(null, $instanceName)->sendText($instanceName, $phone, $message);
                }
            );

            return (bool) $result;
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('Admin WhatsApp notification failed: ' . $e->getMessage(), ['phone' => $phone]);
            return false;
        }
    }
}
