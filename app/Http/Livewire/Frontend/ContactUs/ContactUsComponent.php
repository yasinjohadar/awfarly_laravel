<?php

namespace App\Http\Livewire\Frontend\ContactUs;

use App\Helpers\Filter;
use App\Models\Requests\ContactForms;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ContactUsComponent extends Component
{
    use LivewireAlert;
    public string $type = 'Enquiry';
    public ?string $name = null;
    public ?string $mobile = null;
    public ?string $whatsapp_mobile = null;
    public ?string $email = null;
    public ?string $message = null;

    protected $rules = [
        'type' => ['required', 'in:Enquiry,Complaint,Suggestion,Payments,Technical support,In-app advertising,Report a problem'],
        'name' => ['required'],
        'mobile' => ['required'],
        'whatsapp_mobile' => ['required'],
        'email' => ['nullable', 'email:rfc,dns'],
        'message' => ['required'],
    ];

    public function render()
    {
        return view('livewire.frontend.contact-us.contact-us');
    }

    public function store()
    {
        $this->validate();

        $data = [
            'type' => Filter::RemoveHtml($this->type),
            'name' => Filter::RemoveHtml($this->name),
            'mobile' => Filter::RemoveHtml($this->mobile),
            'whatsappMobile' => Filter::RemoveHtml($this->whatsapp_mobile),
            'email' => Filter::RemoveHtml($this->email),
            'message' => nl2br(Filter::RemoveHtml($this->message)),
        ];

        DB::beginTransaction();
        try {
            ContactForms::create($data);
        } catch (Exception $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
        $this->resetValidation();

        $this->reset([
            'type',
            'name',
            'mobile',
            'whatsapp_mobile',
            'email',
            'message',
        ]);
        $this->alert('success', __('toastr.sent', ['type' => __('frontend/contact-us/contact-us.inputs.message')]), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}
