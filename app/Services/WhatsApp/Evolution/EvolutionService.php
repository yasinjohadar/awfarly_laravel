<?php

namespace App\Services\WhatsApp\Evolution;

use App\Helpers\Settings;
use App\Models\Settings\Setting;
use App\Models\WhatsApp\EvolutionInstance;

class EvolutionService
{
    /**
     * Global Evolution connection settings — same `settings` table / plain-text
     * convention already used for `firebase.credentials.file` (see
     * FirebaseSettingsComponent), just under a `whatsapp.*` key namespace.
     *
     * @return array{evolution_base_url: string, evolution_api_key: string, evolution_instance_name: string}
     */
    public function getSettings(): array
    {
        return [
            'evolution_base_url' => (string) Settings::Get('whatsapp.evolution_base_url', ''),
            'evolution_api_key' => (string) Settings::Get('whatsapp.evolution_api_key', ''),
            'evolution_instance_name' => (string) Settings::Get('whatsapp.evolution_instance_name', ''),
        ];
    }

    public function saveSettings(string $baseUrl, string $apiKey, string $instanceName): void
    {
        $this->setSetting('whatsapp.evolution_base_url', 'Evolution API Base URL', rtrim(trim($baseUrl), '/'));

        // A blank apiKey means "keep the existing one" — the field is left blank in the
        // form on every reload for the same reason a password field never echoes back.
        if (trim($apiKey) !== '') {
            $this->setSetting('whatsapp.evolution_api_key', 'Evolution API Key', trim($apiKey));
        }

        $this->setSetting('whatsapp.evolution_instance_name', 'Evolution Default Instance', trim($instanceName));
    }

    private function setSetting(string $key, string $name, string $value): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        if (!$setting->exists) {
            $setting->name = $name;
            $setting->type = 'whatsapp';
            $setting->value_type = 'string';
        }
        $setting->value = $value;
        $setting->save();
    }

    public function client(?array $override = null): EvolutionApiClient
    {
        $settings = $override ?? $this->getSettings();

        return EvolutionApiClient::fromConfig([
            'base_url' => $settings['evolution_base_url'] ?? '',
            'api_key' => $settings['evolution_api_key'] ?? '',
        ]);
    }

    public function clientFor(?EvolutionInstance $instance = null, ?string $instanceName = null): EvolutionApiClient
    {
        if ($instanceName !== null && $instance === null) {
            $instance = EvolutionInstance::where('instance_name', $instanceName)->first();
        }

        if ($instance instanceof EvolutionInstance && $instance->hasCustomCredentials()) {
            return EvolutionApiClient::fromConfig($instance->resolveApiConfig());
        }

        return $this->client();
    }

    public function clientForActiveInstance(): EvolutionApiClient
    {
        return $this->clientFor(null, $this->activeInstanceName());
    }

    public function refreshInstanceFromApi(EvolutionInstance $instance): EvolutionInstance
    {
        $client = $this->clientFor($instance);

        // The instance list comes first: it carries the profile, it is the fallback when
        // the state endpoint answers in a shape we cannot read, and it is the only way to
        // tell "this phone is disconnected" apart from "this name is not on this server".
        $profile = $this->fetchInstanceProfileFromApi($client, $instance->instance_name);
        $updates = $profile['updates'];

        $connection = null;
        try {
            $connection = EvolutionInstanceState::readConnectionState(
                $client->getConnectionState($instance->instance_name)
            );
        } catch (\Throwable $e) {
            // A 404 here means the name is unknown to this server, which the list already
            // told us. Anything else is a real transport failure and must surface.
            if (! EvolutionApiException::isNotFound($e)) {
                throw $e;
            }
        }

        // Order of trust: the dedicated state endpoint, then the list's connectionStatus,
        // then "the server does not know this name at all". Never an invented "close".
        $connection ??= $profile['connection_status'];
        if ($connection === null && $profile['found'] === false) {
            $connection = EvolutionInstanceState::NOT_FOUND;
        }

        if ($connection !== null) {
            $updates['connection_status'] = $connection;

            if ($connection === EvolutionInstanceState::OPEN) {
                $updates['disconnected_at'] = null;
                if ($instance->connection_status !== EvolutionInstanceState::OPEN) {
                    $updates['connected_at'] = now();
                    $updates['rotation_enabled'] = true;
                }
            } else {
                // connected_at is history and is left alone; only the last-seen-down moves.
                $updates['disconnected_at'] = now();
            }
        }

        if ($updates !== []) {
            $instance->update($updates);
        }

        return $instance->fresh();
    }

    /**
     * Names this Evolution server reports, for the "your name matches none of these"
     * hint. Returns null when the list could not be read at all.
     *
     * @return list<string>|null
     */
    public function remoteInstanceNames(?EvolutionInstance $instance = null): ?array
    {
        try {
            return EvolutionInstanceState::names($this->clientFor($instance)->fetchInstances());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Refresh every instance registered in the platform (uses per-instance API credentials).
     *
     * @return EvolutionInstance[]
     */
    public function syncAllRegisteredInstances(): array
    {
        $synced = [];

        EvolutionInstance::query()
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (EvolutionInstance $instance) use (&$synced) {
                try {
                    $synced[] = $this->refreshInstanceFromApi($instance);
                } catch (\Throwable) {
                    // keep last known row when this instance's API is unreachable
                }
            });

        return $synced;
    }

    /**
     * Look this instance up in the server's instance list.
     *
     * `found` is three-valued on purpose: true (row present), false (the server answered
     * but this name is not in the list), null (the list itself could not be read, so we
     * know nothing and must not conclude anything).
     *
     * @return array{updates: array<string, mixed>, connection_status: ?string, found: ?bool}
     */
    private function fetchInstanceProfileFromApi(EvolutionApiClient $client, string $instanceName): array
    {
        $result = ['updates' => [], 'connection_status' => null, 'found' => null];

        try {
            $response = $client->fetchInstances($instanceName);
        } catch (\Throwable $e) {
            // 404 from the list endpoint is still an answer: the name is not there.
            if (EvolutionApiException::isNotFound($e)) {
                $result['found'] = false;
            }

            return $result;
        }

        $row = EvolutionInstanceState::findRow($response, $instanceName);
        if ($row === null) {
            $result['found'] = false;

            return $result;
        }

        $result['found'] = true;
        $result['connection_status'] = EvolutionInstanceState::readConnectionState($row);

        $updates = [];

        // Only non-empty values are written: a build that omits profileName must not wipe
        // the name we already learned from a previous sync.
        if (! empty($row['id'])) {
            $updates['evolution_uuid'] = $row['id'];
        }

        $ownerJid = EvolutionInstanceState::ownerJid($row);
        if ($ownerJid !== null) {
            $updates['owner_jid'] = $ownerJid;
        }

        if (! empty($row['profileName'])) {
            $updates['profile_name'] = $row['profileName'];
        }

        if (! empty($row['profilePicUrl'])) {
            $updates['profile_pic_url'] = $row['profilePicUrl'];
        }

        $phone = EvolutionInstanceState::phoneNumber($row);
        if ($phone !== null) {
            $updates['phone_number'] = $phone;
        }

        $result['updates'] = $updates;

        return $result;
    }

    /**
     * Refresh connection status for instances before rotation.
     */
    public function refreshRotationCandidates(): int
    {
        return count($this->syncAllRegisteredInstances());
    }

    /**
     * @return string[]
     */
    public function parseInstanceNamesList(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($line) => trim($line),
            $lines
        ))));
    }

    public function registerManualInstance(array $data): EvolutionInstance
    {
        $name = trim((string) ($data['instance_name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('اسم Instance مطلوب.');
        }

        $instance = EvolutionInstance::firstOrNew(['instance_name' => $name]);
        $isNew = ! $instance->exists;
        $instance->fill([
            'label' => trim((string) ($data['label'] ?? '')) ?: null,
            'is_manual' => true,
            'connection_status' => $instance->connection_status ?: 'pending',
            'rotation_enabled' => $isNew ? true : $instance->rotation_enabled,
        ]);

        $baseUrl = trim((string) ($data['evolution_base_url'] ?? ''));
        if ($baseUrl !== '') {
            $instance->evolution_base_url = rtrim($baseUrl, '/');
        }

        $apiKey = trim((string) ($data['evolution_api_key'] ?? ''));
        if ($apiKey !== '') {
            $instance->evolution_api_key = $apiKey;
        }

        $instance->save();

        if (! empty($data['verify']) && ($instance->hasCustomCredentials() || $this->hasGlobalCredentials())) {
            try {
                $this->refreshInstanceFromApi($instance);
            } catch (\Throwable) {
                // keep manual row even if verify fails
            }
        }

        if (! empty($data['set_as_default'])) {
            $this->assignDefaultInstance($instance->instance_name);
        }

        return $instance->fresh();
    }

    public function assignDefaultInstance(string $instanceName): void
    {
        $this->setSetting('whatsapp.evolution_instance_name', 'Evolution Default Instance', $instanceName);

        EvolutionInstance::query()->update(['is_default' => false]);
        EvolutionInstance::where('instance_name', $instanceName)->update(['is_default' => true]);
    }

    public function hasGlobalCredentials(): bool
    {
        $settings = $this->getSettings();

        return ($settings['evolution_base_url'] ?? '') !== '' && ($settings['evolution_api_key'] ?? '') !== '';
    }

    public function activeInstanceName(): string
    {
        $settings = $this->getSettings();
        if (! empty($settings['evolution_instance_name'])) {
            return $settings['evolution_instance_name'];
        }

        return EvolutionInstance::defaultInstance()?->instance_name ?? '';
    }

    public function syncInstances(bool $markConfiguredAsDefault = true): array
    {
        $settings = $this->getSettings();
        $configuredName = $settings['evolution_instance_name'] ?? '';
        $list = EvolutionInstanceState::rows($this->client()->fetchInstances());

        if ($list === []) {
            return [];
        }

        $synced = [];
        $remoteNames = [];

        foreach ($list as $instance) {
            $name = EvolutionInstanceState::rowName($instance);
            if ($name === '') {
                continue;
            }

            $remoteNames[] = $name;

            try {
                $synced[] = EvolutionInstance::syncFromApiArray(
                    $instance,
                    $markConfiguredAsDefault && $name === $configuredName
                );
            } catch (\Throwable) {
                // Table may not exist until migration runs
            }
        }

        if ($remoteNames !== []) {
            EvolutionInstance::query()
                ->where('is_manual', false)
                ->whereNotIn('instance_name', $remoteNames)
                ->delete();
        }

        return $synced;
    }
}
