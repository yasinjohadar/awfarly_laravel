<?php

namespace App\Services\WhatsApp\Evolution;

use App\Helpers\Settings;
use App\Models\WhatsApp\EvolutionInstance;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Picks which connected/rotation-enabled instance an outbound send should use, trying
 * each candidate in the pool (least-recently-used first) until one succeeds — a single
 * flaky number never blocks sending, it's just skipped in favor of the next one.
 */
class EvolutionRotatingSendService
{
    public function __construct(
        private EvolutionInstanceRotator $rotator,
        private EvolutionService $evolutionService,
    ) {}

    public function isRotationActive(): bool
    {
        return (bool) Settings::Get('whatsapp.evolution_rotation_enabled', true);
    }

    /**
     * @template T
     *
     * @param  callable(string $instanceName): T  $sendFn
     * @return array{result: T, instance_name: string}
     */
    public function sendWithRotation(callable $sendFn, ?string $forcedInstanceName = null): array
    {
        if ($forcedInstanceName !== null && $forcedInstanceName !== '') {
            return [
                'result' => $sendFn($forcedInstanceName),
                'instance_name' => $forcedInstanceName,
            ];
        }

        if (! $this->isRotationActive()) {
            $instanceName = $this->fallbackInstanceName();
            if ($instanceName === '') {
                throw new EvolutionApiException(
                    'لم يُحدَّد Instance افتراضي لـ Evolution API. راجع الإعدادات.',
                    'No default Evolution instance configured.',
                );
            }

            return [
                'result' => $sendFn($instanceName),
                'instance_name' => $instanceName,
            ];
        }

        $this->evolutionService->refreshRotationCandidates();
        $pool = $this->rotator->orderedPoolForFailover(true);

        // No number is registered/eligible for rotation (e.g. only the single default
        // instance is configured and was never synced from the "Numbers" page) — fall
        // back to the configured default instance instead of failing outright.
        if ($pool->isEmpty()) {
            $instanceName = $this->fallbackInstanceName();
            if ($instanceName === '') {
                throw new EvolutionApiException(
                    'لا توجد أرقام متصلة ومفعّلة للتبديل. اربط رقماً إضافياً (open) وفعّل التبديل من صفحة أرقام WhatsApp.',
                    'Rotation pool is empty.',
                );
            }

            return [
                'result' => $sendFn($instanceName),
                'instance_name' => $instanceName,
            ];
        }

        $lastException = null;

        foreach ($pool as $instance) {
            try {
                $result = $sendFn($instance->instance_name);
                $this->rotator->markUsed($instance);

                Log::channel('whatsapp')->info('Evolution rotation send succeeded', [
                    'instance' => $instance->instance_name,
                    'phone' => $instance->phone_number,
                ]);

                return [
                    'result' => $result,
                    'instance_name' => $instance->instance_name,
                ];
            } catch (Throwable $e) {
                $lastException = $e;
                Log::channel('whatsapp')->warning('Evolution rotation send failed, trying next instance', [
                    'instance' => $instance->instance_name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        throw new EvolutionApiException(
            'فشل الإرسال عبر جميع أرقام التبديل المتاحة ('.$pool->count().'). راجع حالة الاتصال في صفحة أرقام WhatsApp.',
            'All rotation-eligible Evolution instances failed.',
        );
    }

    public function fallbackInstanceName(): string
    {
        $configured = trim((string) Settings::Get('whatsapp.evolution_instance_name', ''));
        if ($configured !== '') {
            return $configured;
        }

        return EvolutionInstance::defaultInstance()?->instance_name ?? '';
    }
}
