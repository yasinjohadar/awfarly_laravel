<?php

namespace App\Http\Livewire\System\WhatsApp;

use App\Helpers\Settings;
use App\Models\Settings\Setting;
use App\Models\WhatsApp\WhatsAppMessageTemplate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class WhatsAppTemplatesComponent extends Component
{
    use LivewireAlert;

    /**
     * purpose key => label (shown in the assignment section and used as the
     * settings key suffix: whatsapp.templates.<purpose>).
     */
    public array $purposes = [
        'register_otp' => 'pages/system/whatsapp.content.templates.purposes.register_otp',
        'login_otp' => 'pages/system/whatsapp.content.templates.purposes.login_otp',
        'password_reset_otp' => 'pages/system/whatsapp.content.templates.purposes.password_reset_otp',
    ];

    public $templates = [];

    /** purpose => selected template id (string, for wire:model binding) */
    public array $purposeSelections = [];

    /** Create/edit modal state */
    public bool $showFormModal = false;
    public ?int $editing_id = null;
    public ?string $form_name = null;
    public ?string $form_content = null;
    public bool $form_is_active = true;

    public array $sampleVars = [
        'code' => '123456',
        'minutes' => '30',
    ];

    public function mount()
    {
        $this->sampleVars['site'] = Settings::Get('site.name', config('app.name'));
        $this->loadTemplates();
        $this->loadAssignments();
    }

    public function render()
    {
        return view('livewire.pages.system.whatsapp.whatsapp-templates-component', [
            'formPreview' => $this->getFormPreviewProperty(),
        ]);
    }

    private function loadTemplates(): void
    {
        $this->templates = WhatsAppMessageTemplate::orderByDesc('is_active')->orderBy('name')->get();
    }

    private function loadAssignments(): void
    {
        foreach (array_keys($this->purposes) as $purpose) {
            $id = Settings::Get("whatsapp.templates.$purpose");
            $this->purposeSelections[$purpose] = $id ? (string) $id : '';
        }
    }

    /**
     * Live preview of the form's content with sample values, passed into the
     * view explicitly by render() as $formPreview.
     */
    private function getFormPreviewProperty(): string
    {
        return strtr((string) $this->form_content, [
            '{code}' => $this->sampleVars['code'],
            '{site}' => $this->sampleVars['site'],
            '{minutes}' => $this->sampleVars['minutes'],
        ]);
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

    public function openCreateModal()
    {
        if (!$this->authorizeEdit()) return null;

        $this->resetValidation();
        $this->editing_id = null;
        $this->form_name = null;
        $this->form_content = null;
        $this->form_is_active = true;
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        if (!$this->authorizeEdit()) return null;

        $template = WhatsAppMessageTemplate::findOrFail($id);

        $this->resetValidation();
        $this->editing_id = $template->id;
        $this->form_name = $template->name;
        $this->form_content = $template->content;
        $this->form_is_active = $template->is_active;
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
        $this->editing_id = null;
        $this->form_name = null;
        $this->form_content = null;
        $this->resetValidation();
    }

    public function saveTemplate()
    {
        if (!$this->authorizeEdit()) return null;

        $this->validate([
            'form_name' => ['required', 'string', 'max:150'],
            'form_content' => ['required', 'string', 'max:2000'],
        ]);

        WhatsAppMessageTemplate::updateOrCreate(
            ['id' => $this->editing_id],
            [
                'name' => $this->form_name,
                'content' => $this->form_content,
                'is_active' => $this->form_is_active,
            ]
        );

        $this->closeFormModal();
        $this->loadTemplates();

        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    public function deleteTemplate($id)
    {
        if (!$this->authorizeEdit()) return null;

        if (in_array((string) $id, $this->purposeSelections, true)) {
            $this->alert('error', __('pages/system/whatsapp.content.templates.in_use'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        WhatsAppMessageTemplate::where('id', $id)->delete();
        $this->loadTemplates();

        $this->alert('success', __('pages/system/whatsapp.content.templates.deleted'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    public function saveAssignments()
    {
        if (!$this->authorizeEdit()) return null;

        $validIds = WhatsAppMessageTemplate::pluck('id')->map(fn ($id) => (string) $id)->all();

        foreach ($this->purposeSelections as $purpose => $templateId) {
            if ($templateId !== '' && !in_array($templateId, $validIds, true)) {
                continue;
            }

            Setting::where('key', "whatsapp.templates.$purpose")->update([
                'value' => $templateId !== '' ? $templateId : '',
            ]);
        }

        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}
