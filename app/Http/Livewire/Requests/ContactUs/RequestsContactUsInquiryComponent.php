<?php

namespace App\Http\Livewire\Requests\ContactUs;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Requests\ContactForms;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class RequestsContactUsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $model = ContactForms::class;
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.requests.contact-us.read';
    public string $page_type = 'all';
    public bool $showConfirmModal = false;
    public ?int $contact_id = null;
    public ?array $ToggleReadTexts = null;
    public bool $has_delete = true;
    public bool $showDeleteModal = false;
    public array $deleteModalTexts;

    public $listeners = ['rerenderDataTable' => 'changeType'];


    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::checkbox(),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::callback('type', function ($type) {
                return __("pages/requests/contact-us/index.content.datatable.types.$type");
            })
                ->label(__('pages/requests/contact-us/index.content.datatable.type'))
                ->filterable()
                ->searchable(),
            Column::name('name')
                ->label(__('pages/requests/contact-us/index.content.datatable.name'))
                ->filterable()
                ->searchable(),
            Column::name('mobile')
                ->label(__('pages/requests/contact-us/index.content.datatable.mobile'))
                ->filterable()
                ->searchable(),
            Column::name('whatsappMobile')
                ->label(__('pages/requests/contact-us/index.content.datatable.whatsapp_mobile'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('email', function ($email) {
                return $email ?: '-';
            })
                ->label(__('pages/requests/contact-us/index.content.datatable.email'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('message', function ($message) {
                return Str::limit(Filter::RemoveHtml($message), 40);
            })
                ->label(__('pages/requests/contact-us/index.content.datatable.message'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('status', function ($status) {
                return ucwords($status);
            })
                ->label(__('pages/requests/contact-us/index.content.datatable.status'))
                ->filterable()
                ->searchable(),
            Column::callback('created_at', function ($created_at) {
                return Carbon::make($created_at)->diffForHumans();
            })
                ->label(__('pages/requests/contact-us/index.content.datatable.created_at'))
                ->filterable()
                ->searchable(),
            Column::callback(['id', 'status'], function ($id, $status) {
                return view('admin.pages.requests.contact-us.table-actions', ['id' => $id, 'status' => $status]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return mixed
     */
    public function builder()
    {
        if ($this->page_type === 'all') {
            return ContactForms::selectRaw('*');
        }
        return ContactForms::where('status', $this->page_type);
    }

    /**
     * @param $params
     */
    public function changeType($params)
    {
        $this->page_type = $params['status'];
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showConfirmModal($id)
    {
        $this->contact_id = $id;

        $contact = ContactForms::findOrFail($id);
        $column = ($contact->status === 'unread') ? 'read' : 'unread';
        $this->ToggleReadTexts = [
            'title' => __("pages/requests/contact-us/inquiry.modal.confirm.title.$column"),
            'content' => __("pages/requests/contact-us/inquiry.modal.confirm.content.$column"),
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

    /**
     * Toggle Mark Read
     */
    public function markAsRead()
    {
        DB::beginTransaction();
        try {
            //get user
            $contact = ContactForms::findOrFail($this->contact_id);

            if ($contact->status === 'read') {
                $status = 'unread';
            } else {
                $status = 'read';
            }
            //add log
            AdminLogs::log('edit', 'contact us', [
                'old' => $contact,
                'new' => [
                    'status' => $status
                ],
            ], "Edit: contact us #$this->contact_id");

            //update user
            $contact->update([
                'status' => $status
            ]);

            $this->closeConfirmModal();

            $this->emitUp('refreshData');
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

    /**
     * show delete modal for selected rows, or a single request by id
     * @param int|null $id
     */
    public function showDeleteModal($id = null)
    {
        if ($id !== null) {
            $this->selected = [(string) $id];
        }

        if (empty($this->selected)) {
            return;
        }

        $this->deleteModalTexts = [
            'title' => __('pages/requests/contact-us/inquiry.modal.delete.title'),
            'content' => count($this->selected) === 1
                ? __('pages/requests/contact-us/inquiry.modal.delete.content_single')
                : __('pages/requests/contact-us/inquiry.modal.delete.content'),
            'cancel' => __('pages/requests/contact-us/inquiry.modal.delete.cancel'),
            'submit' => __('pages/requests/contact-us/inquiry.modal.delete.submit'),
        ];
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('requests.contact.us')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        if (empty($this->selected)) {
            $this->showDeleteModal = false;
            return null;
        }

        DB::beginTransaction();
        try {
            $contacts = ContactForms::whereIn('id', $this->selected)->get();

            ContactForms::whereIn('id', $this->selected)->delete();

            $this->selected = [];

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            AdminLogs::log('delete', 'contact us', [
                'contacts' => $contacts,
            ], 'Delete: contact us requests');

            $this->showDeleteModal = false;

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
    }
}
