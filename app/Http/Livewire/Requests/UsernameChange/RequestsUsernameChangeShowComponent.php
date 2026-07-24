<?php

namespace App\Http\Livewire\Requests\UsernameChange;

use App\Helpers\Admins\AdminLogs;
use App\Models\Requests\UsernameRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;
use Validator;

class RequestsUsernameChangeShowComponent extends Component
{
    use LivewireAlert;

    public int $request_id;
    public bool $showConfirmModal = false;
    public UsernameRequests $request;
    public ?string $confirm_type = null;


    public function render()
    {
        $this->request = UsernameRequests::find($this->request_id);

        return view('admin.pages.requests.change-username.inquiry', ['request' => $this->request]);
    }

    /**
     * show edit modal
     * @param $id
     * @param $type
     */
    public function showConfirmModal($id, $type)
    {
        $this->confirm_type = $type;

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
        $this->reset('confirm_type');
    }

    /**
     */
    public function updateRequest()
    {
        $validator = Validator::make(
            ['confirm_type' => $this->confirm_type],
            ['confirm_type' => 'required|in:declined,approved'],
        );

        if ($validator->fails()) {
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $validator->errors()->first(),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {
            //get user
            $contact = UsernameRequests::with('user')
                ->findOrFail($this->request_id);

            //update user
            $contact->update([
                'status' => $this->confirm_type
            ]);

            if ($this->confirm_type === 'approved') {

                $this->request->user()
                    ->update([
                        'username' => $this->request['new_username'],
                    ]);
            }

            //add log
            AdminLogs::log('edit', 'username change', [
                'old' => $contact,
                'new' => [
                    'status' => $this->confirm_type,
                    'username' => $this->request['new_username'],
                ],
            ], "Edit: username change #$this->request_id");

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
