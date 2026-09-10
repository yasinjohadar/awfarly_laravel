<?php

namespace App\Http\Livewire\System\WhatsApp;

use App\Services\WhatsApp\Evolution\EvolutionApiException;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class WhatsAppSendComponent extends Component
{
    use LivewireAlert;

    /// active tab: text|media|advanced
    public string $type = 'text';

    /// text
    public ?string $text_to = null;
    public ?string $text_message = null;

    /// media
    public ?string $media_to = null;
    public string $media_type = 'image';
    public ?string $media_file_name = null;
    public ?string $media_url = null;
    public ?string $media_caption = null;

    /// advanced
    public string $advanced_type = 'buttons';
    public ?string $advanced_to = null;
    public ?string $advanced_payload = null;

    public int $rotationPoolCount = 0;

    public function mount(EvolutionInstanceRotator $rotator)
    {
        $this->rotationPoolCount = $rotator->poolCount();
    }

    public function render()
    {
        return view('livewire.pages.system.whatsapp.whatsapp-send-component');
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    private function authorizeEdit(): bool
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return false;
        }
        return true;
    }

    public function sendText(EvolutionRotatingSendService $sendService, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate([
            'text_to' => ['required', 'string'],
            'text_message' => ['required', 'string'],
        ]);

        try {
            $result = $sendService->sendWithRotation(function (string $instanceName) use ($evolutionService) {
                return $evolutionService->clientFor(null, $instanceName)->sendText(
                    $instanceName,
                    $this->text_to,
                    $this->text_message
                );
            });

            $this->text_message = null;
            $this->alert('success', __('pages/system/whatsapp.content.send.success', ['instance' => $result['instance_name']]), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            $this->reportError($e);
        }
    }

    public function sendMedia(EvolutionRotatingSendService $sendService, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate([
            'media_to' => ['required', 'string'],
            'media_type' => ['required', 'in:image,video,audio,document'],
            'media_url' => ['required', 'string'],
        ]);

        try {
            $result = $sendService->sendWithRotation(function (string $instanceName) use ($evolutionService) {
                return $evolutionService->clientFor(null, $instanceName)->sendMedia($instanceName, array_filter([
                    'number' => $this->media_to,
                    'mediatype' => $this->media_type,
                    'fileName' => $this->media_file_name,
                    'media' => $this->media_url,
                    'caption' => $this->media_caption,
                ]));
            });

            $this->alert('success', __('pages/system/whatsapp.content.send.success', ['instance' => $result['instance_name']]), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            $this->reportError($e);
        }
    }

    public function sendAdvanced(EvolutionRotatingSendService $sendService, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate([
            'advanced_to' => ['required', 'string'],
            'advanced_payload' => ['required', 'string'],
        ]);

        $payload = json_decode((string) $this->advanced_payload, true);
        if (!is_array($payload)) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/system/whatsapp.content.send.invalid_json'),
            ]);
            return null;
        }

        $payload = array_merge(['number' => $this->advanced_to], $payload);
        $method = match ($this->advanced_type) {
            'buttons' => 'sendButtons',
            'list' => 'sendList',
            'poll' => 'sendPoll',
            'location' => 'sendLocation',
            'contact' => 'sendContact',
            'sticker' => 'sendSticker',
            'status' => 'sendStatus',
            default => 'sendButtons',
        };

        try {
            $result = $sendService->sendWithRotation(function (string $instanceName) use ($evolutionService, $method, $payload) {
                return $evolutionService->clientFor(null, $instanceName)->{$method}($instanceName, $payload);
            });

            $this->alert('success', __('pages/system/whatsapp.content.send.success', ['instance' => $result['instance_name']]), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            $this->reportError($e);
        }
    }

    private function reportError(Throwable $e): void
    {
        Log::channel('whatsapp')->error('WhatsAppSendComponent: send failed', ['exception' => $e->getMessage()]);
        $this->alert('error', __('toastr.error'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            'text' => EvolutionApiException::resolveUserMessage($e),
        ]);
    }
}
