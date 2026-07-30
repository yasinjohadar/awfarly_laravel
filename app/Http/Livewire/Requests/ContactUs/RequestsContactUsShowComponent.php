<?php

namespace App\Http\Livewire\Requests\ContactUs;

use App\Helpers\Admins\AdminLogs;
use App\Models\Requests\ContactForms;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class RequestsContactUsShowComponent extends Component
{
    use LivewireAlert;

    public int $contact_id;
    public bool $showConfirmModal = false;
    public bool $showDeleteModal = false;
    public ContactForms $contact;
    public ?array $ToggleReadTexts = null;
    public array $deleteModalTexts = [];

    public function render()
    {
        $this->contact = ContactForms::findOrFail($this->contact_id);

        return view('admin.pages.requests.contact-us.inquiry', ['contact' => $this->contact]);
    }

    public function showConfirmModal($id = null)
    {
        $contact = ContactForms::findOrFail($this->contact_id);
        $column = ($contact->status === 'unread') ? 'read' : 'unread';

        $this->ToggleReadTexts = [
            'title' => __("pages/requests/contact-us/inquiry.modal.confirm.title.$column"),
            'content' => __("pages/requests/contact-us/inquiry.modal.confirm.content.$column"),
        ];

        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    public function markAsRead()
    {
        if (!Auth::guard('admin')->user()->can('requests.contact.us')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {
            $contact = ContactForms::findOrFail($this->contact_id);
            $status = $contact->status === 'read' ? 'unread' : 'read';

            AdminLogs::log('edit', 'contact us', [
                'old' => $contact,
                'new' => [
                    'status' => $status,
                ],
            ], "Edit: contact us #$this->contact_id");

            $contact->update([
                'status' => $status,
            ]);

            $this->closeConfirmModal();
            $this->emitUp('refreshData');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        DB::commit();
        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }

    public function showDeleteModal()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/requests/contact-us/inquiry.modal.delete.title'),
            'content' => __('pages/requests/contact-us/inquiry.modal.delete.content_single'),
            'cancel' => __('pages/requests/contact-us/inquiry.modal.delete.cancel'),
            'submit' => __('pages/requests/contact-us/inquiry.modal.delete.submit'),
        ];
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    public function deleteContact()
    {
        if (!Auth::guard('admin')->user()->can('requests.contact.us')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {
            $contact = ContactForms::findOrFail($this->contact_id);

            AdminLogs::log('delete', 'contact us', [
                'contacts' => $contact,
            ], "Delete: contact us #$this->contact_id");

            $contact->delete();

            $this->closeDeleteModal();
            $this->emitUp('refreshData');
            $this->emitUp('setContactId', null);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        DB::commit();
        $this->alert('success', __('toastr.delete'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}
