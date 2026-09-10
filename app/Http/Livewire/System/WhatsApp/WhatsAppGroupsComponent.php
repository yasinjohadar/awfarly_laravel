<?php

namespace App\Http\Livewire\System\WhatsApp;

use App\Services\WhatsApp\Evolution\EvolutionApiException;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\Evolution\EvolutionService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class WhatsAppGroupsComponent extends Component
{
    use LivewireAlert;

    public array $groups = [];
    public ?string $activeInstanceName = null;
    public ?string $loadError = null;

    /// selected group + its members
    public ?string $selectedGroupJid = null;
    public ?string $selectedGroupName = null;
    public array $members = [];
    public ?string $memberSearch = null;

    /// send-to-group form
    public ?string $group_message = null;

    /// send-to-member modal
    public bool $showMemberMessageModal = false;
    public ?string $member_message_to = null;
    public ?string $member_message_name = null;
    public ?string $member_message_text = null;

    public function mount(EvolutionService $evolutionService)
    {
        $this->activeInstanceName = $evolutionService->activeInstanceName();
        $this->loadGroups($evolutionService);
    }

    public function render()
    {
        return view('livewire.pages.system.whatsapp.whatsapp-groups-component');
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

    public function loadGroups(EvolutionService $evolutionService)
    {
        $this->loadError = null;

        if (!$this->activeInstanceName) {
            $this->loadError = __('pages/system/whatsapp.content.groups.no_instance');
            return null;
        }

        try {
            $response = $evolutionService->clientForActiveInstance()->fetchAllGroups($this->activeInstanceName);
            $rows = array_is_list($response) ? $response : ($response['data'] ?? $response['groups'] ?? []);

            $this->groups = collect($rows)
                ->filter(fn ($g) => is_array($g))
                ->map(fn ($g) => [
                    'jid' => $g['id'] ?? $g['jid'] ?? '',
                    'subject' => $g['subject'] ?? $g['name'] ?? '—',
                    'size' => $g['size'] ?? $g['participants'] ?? null,
                    'announce' => (bool) ($g['announce'] ?? false),
                ])
                ->filter(fn ($g) => $g['jid'] !== '')
                ->values()
                ->all();
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('WhatsAppGroupsComponent: fetch groups failed', ['exception' => $e->getMessage()]);
            $this->loadError = EvolutionApiException::resolveUserMessage($e);
        }
    }

    public function selectGroup($jid, $name, EvolutionService $evolutionService)
    {
        $this->selectedGroupJid = $jid;
        $this->selectedGroupName = $name;
        $this->members = [];
        $this->memberSearch = null;

        try {
            $response = $evolutionService->clientForActiveInstance()->findGroupMembers($this->activeInstanceName, $jid);
            $rows = $response['participants'] ?? $response['data'] ?? (array_is_list($response) ? $response : []);

            $this->members = collect($rows)
                ->filter(fn ($m) => is_array($m))
                ->map(function ($m) {
                    $id = $m['id'] ?? $m['jid'] ?? '';
                    $phone = is_string($id) ? strtok($id, '@') : null;

                    return [
                        'jid' => $id,
                        'phone' => $phone ?: null,
                        'is_admin' => in_array($m['admin'] ?? null, ['admin', 'superadmin'], true),
                    ];
                })
                ->filter(fn ($m) => $m['jid'] !== '')
                ->values()
                ->all();
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('WhatsAppGroupsComponent: fetch members failed', ['exception' => $e->getMessage()]);
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => EvolutionApiException::resolveUserMessage($e),
            ]);
        }
    }

    public function backToList()
    {
        $this->selectedGroupJid = null;
        $this->selectedGroupName = null;
        $this->members = [];
        $this->group_message = null;
    }

    public function sendToGroup(EvolutionRotatingSendService $sendService, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate(['group_message' => ['required', 'string']]);

        try {
            $sendService->sendWithRotation(function (string $instanceName) use ($evolutionService) {
                return $evolutionService->clientFor(null, $instanceName)->sendText(
                    $instanceName,
                    $this->selectedGroupJid,
                    $this->group_message
                );
            }, $this->activeInstanceName);

            $this->group_message = null;
            $this->alert('success', __('pages/system/whatsapp.content.groups.sent'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('WhatsAppGroupsComponent: send to group failed', ['exception' => $e->getMessage()]);
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => EvolutionApiException::resolveUserMessage($e),
            ]);
        }
    }

    public function openMemberMessage($jid, $phone)
    {
        $this->member_message_to = $jid;
        $this->member_message_name = $phone;
        $this->member_message_text = null;
        $this->showMemberMessageModal = true;
    }

    public function closeMemberMessage()
    {
        $this->showMemberMessageModal = false;
        $this->member_message_to = null;
        $this->member_message_name = null;
        $this->member_message_text = null;
    }

    public function sendToMember(EvolutionRotatingSendService $sendService, EvolutionService $evolutionService)
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate(['member_message_text' => ['required', 'string']]);

        try {
            $sendService->sendWithRotation(function (string $instanceName) use ($evolutionService) {
                return $evolutionService->clientFor(null, $instanceName)->sendText(
                    $instanceName,
                    $this->member_message_to,
                    $this->member_message_text
                );
            }, $this->activeInstanceName);

            $this->alert('success', __('pages/system/whatsapp.content.groups.sent'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->closeMemberMessage();
        } catch (Throwable $e) {
            Log::channel('whatsapp')->error('WhatsAppGroupsComponent: send to member failed', ['exception' => $e->getMessage()]);
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => EvolutionApiException::resolveUserMessage($e),
            ]);
        }
    }
}
