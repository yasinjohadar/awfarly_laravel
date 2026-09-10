<?php

namespace App\Http\Livewire\System\WhatsApp;

use App\Models\WhatsApp\EvolutionInstance;
use App\Services\WhatsApp\Evolution\EvolutionApiException;
use App\Services\WhatsApp\Evolution\EvolutionInstanceState;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class WhatsAppInstancesComponent extends Component
{
    use LivewireAlert;

    public $instances = [];
    public int $rotationPoolCount = 0;
    public int $connectedCount = 0;

    /// Add-instance form
    public ?string $new_instance_name = null;
    public bool $new_set_as_default = false;

    /// QR/connect modal
    public bool $showConnectModal = false;
    public ?string $connecting_instance_name = null;
    public ?string $qr_code = null;
    public ?string $connection_status = null;

    public function mount()
    {
        $this->loadInstances();
    }

    public function render()
    {
        return view('livewire.pages.system.whatsapp.whatsapp-instances-component');
    }

    private function loadInstances(): void
    {
        $this->instances = EvolutionInstance::orderByDesc('is_default')
            ->orderByDesc('is_manual')
            ->orderBy('instance_name')
            ->get();

        $this->rotationPoolCount = EvolutionInstance::rotationPoolCount();
        $this->connectedCount = $this->instances->filter(fn ($i) => $i->isConnected())->count();
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

    private function reportError(string $prefix, Throwable $e): void
    {
        Log::channel('whatsapp')->error($prefix, ['exception' => $e->getMessage()]);
        $this->alert('error', __('toastr.error'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            'text' => EvolutionApiException::resolveUserMessage($e),
        ]);
    }

    /**
     * Create the instance on Evolution itself, register it locally, then immediately
     * open the QR modal — mirrors EvolutionInstanceController::store()+connect().
     */
    public function addInstance(EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate([
            'new_instance_name' => ['required', 'string', 'max:150'],
        ]);

        $instanceName = trim($this->new_instance_name);

        try {
            $evolutionService->clientFor(null, $instanceName)->createInstance([
                'instanceName' => $instanceName,
                'integration' => 'WHATSAPP-BAILEYS',
                'qrcode' => true,
            ]);

            $evolutionService->registerManualInstance([
                'instance_name' => $instanceName,
                'verify' => false,
                'set_as_default' => $this->new_set_as_default,
            ]);

            $evolutionService->syncInstances($this->new_set_as_default);

            $this->new_instance_name = null;
            $this->new_set_as_default = false;
            $this->loadInstances();

            $this->alert('success', __('pages/system/whatsapp.content.instances.created'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->showConnect($instanceName, $evolutionService);
        } catch (Throwable $e) {
            $this->reportError('WhatsAppInstancesComponent: create instance failed', $e);
        }
    }

    public function syncStatus(EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        try {
            $evolutionService->syncInstances(false);
        } catch (Throwable $e) {
            // global discovery failing is not fatal — instances with their own
            // credentials still get refreshed below (same reasoning as claudHosting's
            // EvolutionInstanceController::sync())
        }

        try {
            $evolutionService->syncAllRegisteredInstances();
            $this->loadInstances();

            $this->alert('success', __('pages/system/whatsapp.content.instances.synced', ['count' => $this->rotationPoolCount]), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            $this->reportError('WhatsAppInstancesComponent: sync failed', $e);
        }
    }

    public function toggleRotation($instanceName)
    {
        if (!$this->authorizeEdit()) return null;

        $instance = EvolutionInstance::where('instance_name', $instanceName)->first();
        if (!$instance) return null;

        $instance->update(['rotation_enabled' => !$instance->rotation_enabled]);
        $this->loadInstances();
    }

    public function setDefault($instanceName, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $evolutionService->assignDefaultInstance($instanceName);
        $this->loadInstances();

        $this->alert('success', __('pages/system/whatsapp.content.instances.set_default', ['name' => $instanceName]), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    public function delete($instanceName, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $instance = EvolutionInstance::where('instance_name', $instanceName)->first();

        if (!$instance?->is_manual) {
            try {
                $evolutionService->clientFor($instance, $instanceName)->deleteInstance($instanceName);
            } catch (Throwable $e) {
                if (!EvolutionApiException::isNotFound($e) && $instance) {
                    $this->reportError('WhatsAppInstancesComponent: delete instance failed', $e);
                    return null;
                }
            }
        }

        $wasDefault = (bool) $instance?->is_default;
        $settingsName = $evolutionService->getSettings()['evolution_instance_name'] ?? '';

        EvolutionInstance::where('instance_name', $instanceName)->delete();

        if ($wasDefault || $settingsName === $instanceName) {
            $replacement = EvolutionInstance::orderByDesc('is_default')
                ->orderByDesc('connection_status')
                ->orderBy('instance_name')
                ->first();

            if ($replacement) {
                $evolutionService->assignDefaultInstance($replacement->instance_name);
            }
        }

        $this->loadInstances();

        $this->alert('success', __('pages/system/whatsapp.content.instances.deleted'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    public function showConnect($instanceName, EvolutionService $evolutionService)
    {
        $instance = EvolutionInstance::where('instance_name', $instanceName)->first();
        if (!$instance) return null;

        $this->connecting_instance_name = $instanceName;
        $this->connection_status = $instance->connection_status;
        $this->qr_code = $instance->qr_code;
        $this->showConnectModal = true;

        $this->fetchQr($evolutionService);
    }

    public function closeConnectModal()
    {
        $this->showConnectModal = false;
        $this->connecting_instance_name = null;
        $this->qr_code = null;
        $this->connection_status = null;
        $this->loadInstances();
    }

    public function fetchQr(EvolutionService $evolutionService)
    {
        if (!$this->connecting_instance_name) return null;

        try {
            $response = $evolutionService->clientFor(null, $this->connecting_instance_name)
                ->connectInstance($this->connecting_instance_name);

            $qr = $response['base64'] ?? $response['qrcode']['base64'] ?? $response['code'] ?? null;

            if ($qr) {
                EvolutionInstance::where('instance_name', $this->connecting_instance_name)->update([
                    'qr_code' => $qr,
                    'connection_status' => 'connecting',
                ]);
                $this->qr_code = $qr;
                $this->connection_status = 'connecting';
            } else {
                $this->alert('warning', __('pages/system/whatsapp.content.instances.connect.no_qr'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
            }
        } catch (Throwable $e) {
            $this->reportError('WhatsAppInstancesComponent: fetch QR failed', $e);
        }
    }

    public function checkStatus(EvolutionService $evolutionService)
    {
        if (!$this->connecting_instance_name) return null;

        try {
            try {
                $evolutionService->syncInstances(false);
            } catch (Throwable $e) {
                // global discovery may fail while this instance uses its own credentials
            }

            $instance = EvolutionInstance::where('instance_name', $this->connecting_instance_name)->first();
            if (!$instance) return null;

            $fresh = $evolutionService->refreshInstanceFromApi($instance);
            $this->connection_status = $fresh->connection_status;

            if ($fresh->connection_status === EvolutionInstanceState::OPEN) {
                $this->alert('success', __('pages/system/whatsapp.content.instances.connect.connected'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
            } elseif ($fresh->connection_status === EvolutionInstanceState::NOT_FOUND) {
                $remote = $evolutionService->remoteInstanceNames($fresh);
                $this->alert('warning', __('pages/system/whatsapp.content.instances.connect.not_found', [
                    'names' => !empty($remote) ? implode('، ', $remote) : '—',
                ]), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
            }
        } catch (Throwable $e) {
            $this->reportError('WhatsAppInstancesComponent: check status failed', $e);
        }
    }
}
