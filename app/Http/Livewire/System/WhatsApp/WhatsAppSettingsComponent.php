<?php

namespace App\Http\Livewire\System\WhatsApp;

use App\Services\WhatsApp\Evolution\EvolutionApiClient;
use App\Services\WhatsApp\Evolution\EvolutionApiException;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class WhatsAppSettingsComponent extends Component
{
    use LivewireAlert;

    public ?string $evolution_base_url = null;
    public ?string $evolution_api_key = null;
    public ?string $evolution_instance_name = null;
    public bool $has_api_key = false;
    public int $rotationPoolCount = 0;

    public function mount(EvolutionService $evolutionService, EvolutionInstanceRotator $rotator)
    {
        $settings = $evolutionService->getSettings();

        $this->evolution_base_url = $settings['evolution_base_url'];
        $this->evolution_instance_name = $settings['evolution_instance_name'];
        $this->has_api_key = $settings['evolution_api_key'] !== '';
        $this->rotationPoolCount = $rotator->poolCount();
    }

    public function render()
    {
        return view('livewire.pages.system.whatsapp.whatsapp-settings-component');
    }

    public function save(EvolutionService $evolutionService)
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'evolution_base_url' => ['required', 'url'],
            'evolution_api_key' => ['nullable', 'string'],
            'evolution_instance_name' => ['nullable', 'string'],
        ]);

        $evolutionService->saveSettings(
            (string) $this->evolution_base_url,
            (string) $this->evolution_api_key,
            (string) $this->evolution_instance_name
        );

        $this->has_api_key = $this->has_api_key || trim((string) $this->evolution_api_key) !== '';
        $this->evolution_api_key = null;

        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    public function testConnection(EvolutionService $evolutionService)
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $baseUrl = trim((string) $this->evolution_base_url);
        if ($baseUrl === '') {
            $this->alert('error', __('pages/system/whatsapp.content.settings.test_connection.failure'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/system/whatsapp.content.settings.test_connection.missing_base_url'),
            ]);
            return null;
        }

        // Test whatever is currently typed in the form — not necessarily what was last
        // saved. A blank API key field falls back to the already-saved key, matching
        // save()'s "blank means keep the current key" convention.
        $apiKey = trim((string) $this->evolution_api_key);
        if ($apiKey === '') {
            $apiKey = $evolutionService->getSettings()['evolution_api_key'];
        }

        try {
            EvolutionApiClient::fromConfig([
                'base_url' => $baseUrl,
                'api_key' => $apiKey,
            ])->getInformation();

            $this->alert('success', __('pages/system/whatsapp.content.settings.test_connection.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('WhatsAppSettingsComponent: connection test failed', ['exception' => $e->getMessage()]);

            $this->alert('error', __('pages/system/whatsapp.content.settings.test_connection.failure'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => EvolutionApiException::resolveUserMessage($e),
            ]);
        }
    }
}
