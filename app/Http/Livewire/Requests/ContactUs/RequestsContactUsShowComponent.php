<?php

namespace App\Http\Livewire\Requests\ContactUs;

use App\Helpers\Admins\AdminLogs;
use App\Models\Requests\ContactForms;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class RequestsContactUsShowComponent extends Component
{
    use LivewireAlert;

    public int $contact_id;
    public bool $showConfirmModal = false;
    public ContactForms $contact;
    public ?array $ToggleReadTexts = null;

    public function render()
    {
        $this->contact = ContactForms::find($this->contact_id);

        return view('admin.pages.requests.contact-us.inquiry', ['contact' => $this->contact]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showConfirmModal($id)
    {
        $this->ToggleReadTexts = [
            'title' => __("pages/requests/contact-us/inquiry.modal.confirm.title.read"),
            'content' => __("pages/requests/contact-us/inquiry.modal.confirm.content.read"),
        ];
        //show the modal
        $this->showConfirmModal = true;

    }

    /**
     * Close Modal
     */
    public function closeConfirmModal()
    {
        //close the modal
        $this->showConfirmModal = false;
    }

    public function markAsRead()
    {
        DB::beginTransaction();
        try {
            //get user
            $contact = ContactForms::findOrFail($this->contact_id);

            //add log
            AdminLogs::log('edit', 'contact us', [
                'old' => $contact,
                'new' => [
                    'status' => 'read'
                ],
            ], "Edit: contact us #$this->contact_id");

            //update user
            $contact->update([
                'status' => 'read'
            ]);

            $this->closeConfirmModal();

        } catch (Throwable $e) {
            DB::rollBack();
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
        //send toastr alert with success
        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}
