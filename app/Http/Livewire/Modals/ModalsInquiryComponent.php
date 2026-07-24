<?php

namespace App\Http\Livewire\Modals;

use Exception;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Throwable;
use Carbon\Carbon;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Modals\Modal;
use Livewire\WithFileUploads;
use App\Helpers\Admins\AdminLogs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ModalsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    use WithFileUploads;


    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.modals.edit';
    public $model = Modal::class;
    public array $modal;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public Collection $modals;
    private string $name_column = '';
    public bool $has_delete = true;

    public $listeners = [
        'setParentModalEdit',
    ];

    /**
     * CustomersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //get admin language
        $this->getAdminLanguage();

        //set modal texts
        $this->setModalTexts();

        $this->getAllModals();

        parent::__construct($id);
    }

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

            Column::name('title_en')
                ->label(__('pages/modals/index.content.datatable.title_en'))
                ->filterable()
                ->searchable(),

            Column::name('title_ar')
                ->label(__('pages/modals/index.content.datatable.title_ar'))
                ->filterable()
                ->searchable(),

            DateColumn::callback('start_at', function ($start_at) {
                return $start_at ? Carbon::parse($start_at)->format('Y-m-d h:i A') : '-';
            })
                ->label(__('pages/modals/index.content.datatable.start_at'))
                ->filterable()
                ->searchable(),

            DateColumn::callback('end_at', function ($end_at) {
                return $end_at ? Carbon::parse($end_at)->format('Y-m-d h:i A') : '-';
            })
                ->label(__('pages/modals/index.content.datatable.end_at'))
                ->filterable()
                ->searchable(),


            Column::callback(['id'], function ($id) {
                return view('admin.pages.modals.table-actions', ['id' => $id, 'parent' => true]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        return Modal::query();
    }

    /**
     * get admin language
     */
    public function getAdminLanguage()
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $this->name_column = 'title_ar';
        } else {
            $this->name_column = 'title_en';
        }
    }

    /**
     * show delete modal
     */
    public function showDeleteModal()
    {
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('modal.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get modals
            $modals = Modal::whereIn('id', $this->selected)
                ->get();

            //delete data
            parent::delete($this->selected);

            //delete images belongs to these modals
            foreach ($modals as $modal) {
                if ($modal->image) {
                    Files::deleteS3File($modal->image);
                }
            }
            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'modals', [
                'modals' => $modals
            ], "Delete: modals");

            //close modal
            $this->showDeleteModal = false;

            $this->emitUp('recountCounters');
        } catch (Throwable $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        //commit
        DB::commit();
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user with data
        $this->modal = Modal::where('id', $id)
            ->first()
            ->toArray();

        $this->modal['start_at'] = $this->modal['start_at'] ? Carbon::parse($this->modal['start_at'])->format('Y-m-d\TH:m') : '-';
        $this->modal['end_at'] = $this->modal['end_at'] ? Carbon::parse($this->modal['end_at'])->format('Y-m-d\TH:m') : '-';
        $this->modals = Modal::where('id', '!=', $id)
            ->get()
            ->map(function ($modal) {
                return [
                    'id' => $modal->id,
                    'name' => $modal[$this->name_column],
                ];
            });

        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Edit');
        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->modal = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('modal.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([

            'modal.recipients_type' => ['required'],
            'modal.link' => ['nullable', 'url'],
            'modal.parent_modal_id' => ['nullable', "exists:modals,id"],
            'modal.title_en' => ['required', "unique:modals,title_en,$id"],
            'modal.title_ar' => ['required', "unique:modals,title_ar,$id"],
            'modal.body_ar' => ['required', 'string'],
            'modal.body_en' => ['required', 'string'],
            'modal.start_at' => ['required', 'string'],
            'modal.end_at' => ['required', 'string'],
        ]);

        //set data
        $data = $this->modal;
        $data['recipients_type'] = Filter::RemoveHtml($this->modal['recipients_type']);
        $data['link'] = $this->modal['link'];
        $data['title_en'] = Filter::RemoveHtml($this->modal['title_en']);
        $data['title_ar'] = Filter::RemoveHtml($this->modal['title_ar']);
        $data['body_ar'] = isset($this->modal['body_ar']) ? Filter::RemoveHtml($this->modal['body_ar']) : null;
        $data['body_en'] = isset($this->modal['body_en']) ? Filter::RemoveHtml($this->modal['body_en']) : null;
        $data['start_at'] = isset($this->modal['start_at']) ? Filter::RemoveHtml($this->modal['start_at']) : null;
        $data['end_at'] = isset($this->modal['end_at']) ? Filter::RemoveHtml($this->modal['end_at']) : null;
        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $modal = Modal::findOrFail($id);
            //add log
            AdminLogs::log('edit', 'modals', [
                'old' => $modal,
                'new' => $data,
            ], "Edit: modal #$id");

            //update user
            $modal->update($data);

            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput');

            $this->emitUp('recountCounters');
        } catch (Exception $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        //commit
        DB::commit();
    }

    /**
     * get all modals
     */
    public function getAllModals()
    {
        $this->modals = Modal::get()
            ->map(function ($modal) {
                return [
                    'id' => $modal->id,
                    'name' => $modal[$this->name_column],
                ];
            });
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/modals/index.modal.delete.title'),
            'content' => __('pages/modals/index.modal.delete.content'),
            'cancel' => __('pages/modals/index.modal.delete.cancel'),
            'submit' => __('pages/modals/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/modals/index.modal.edit.title'),
            'cancel' => __('pages/modals/index.modal.edit.cancel'),
            'submit' => __('pages/modals/index.modal.edit.submit'),
        ];
    }

    /**
     * set the value of parent modal id once it changes
     * @param $parent_modal_id
     */
    public function setParentModalEdit($parent_modal_id)
    {
        $this->modal['parent_modal_id'] = $parent_modal_id;

        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Edit');
    }

    /**
     * dispatch select2 modal while updating
     */
    public function updating()
    {
        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Edit');
    }
}
