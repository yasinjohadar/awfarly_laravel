<?php

namespace App\Http\Livewire\Modals;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Modals\Modal;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class ModalInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    use WithFileUploads;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    // public $afterTableSlot = 'modals.modals.modals';
    //public string $afterTableSlot2 = 'modals.modals.sub-modals.add';
    public $model = Modal::class;
    public array $modals;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showAddModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    private string $name_column = '';
    public bool $has_delete = true;
    public ?int $modals_id = null;
    public array $subModal = [];

    protected $listeners = [
        'showAddModal'
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
            Column::callback(['image'], function ($image) {
                return $image ? "
                <div class='text-center'>
                    <a class='text-center' target='_blank' href='" . route('modals.image.get', $image) . "'>
                        <img src='" . route('modals.image.get', $image) . "' class='rounded-circle' height='50' width='50'/>
                    </a>
                </div>" : '<div class="text-center">-</div>';
            })
                ->label(__('pages/modals/index.content.datatable.image')),
            Column::name('title_en')
                ->label(__('pages/modals/index.content.datatable.title_en'))
                ->filterable()
                ->searchable(),
            Column::name('title_ar')
                ->label(__('pages/modals/index.content.datatable.title_ar'))
                ->filterable()
                ->searchable(),
            /*Column::name('description')
                ->label(__('pages/modals/index.content.datatable.description'))
                ->filterable()
                ->searchable(),*/
            BooleanColumn::name('is_active')
                ->label(__('pages/modals/index.content.datatable.active'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.modals.table-actions', ['id' => $id]);
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
        return Modal::where('parent_modals_id', $this->modals_id);
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
            foreach ($modals as $modals) {
                if ($modals->image) {
                    Files::deleteS3File($modals->image);
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
        $this->modals = Modal::where('id', $id)
            ->first()
            ->toArray();

        $this->modals['is_active'] = $this->modals['is_active'] ? 1 : 0;
        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->modals = [];

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
            'modals.title_en' => ['required'],
            'modals.title_ar' => ['required'],
            'modals.description' => ['nullable'],
            'modals.new_image' => ['nullable'],
            'modals.is_active' => ['boolean'],
        ]);

        //set data
        $data = $this->modals;
        $data['title_en'] = Filter::RemoveHtml($this->modals['title_en']);
        $data['title_ar'] = Filter::RemoveHtml($this->modals['title_ar']);
        $data['description'] = isset($this->modals['description']) ? Filter::RemoveHtml($this->modals['description']) : null;
        $data['is_active'] = $this->modals['is_active'];

        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $modals = Modal::findOrFail($id);

            //check if admin chosen image or not then upload it
            if (isset($this->modals['new_image']) && $this->modals['new_image'] != null) {
                //check if modals had a previous image
                if ($modals->image) {
                    Files::deleteS3File($modals->image);
                }
                $data['image'] = $this->modals['new_image']->store('uploads/modals', 'local');
            }

            //add log
            AdminLogs::log('edit', 'modals', [
                'old' => $modals,
                'new' => $data,
            ], "Edit: modals #$id");

            //update user
            $modals->update($data);

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
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('modal.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate([
            'modals_id' => ['required', "exists:modals,id"],
            'subModal.title_en' => ['required', "unique:modals,title_en"],
            'subModal.title_ar' => ['required', "unique:modals,title_ar"],
            'subModal.description' => ['nullable'],
            'subModal.modals_image' => ['nullable'],
        ]);


        DB::beginTransaction();
        try {
            if (isset($this->subModal['modals_image'])) {
                $url = $this->subModal['modals_image']->store('uploads/modals', 'local');
            } else {
                $url = null;
            }

            $data = [
                'parent_modals_id' => $this->modals_id,
                'title_en' => Filter::RemoveHtml($this->subModal['title_en']),
                'title_ar' => Filter::RemoveHtml($this->subModal['title_ar']),
                'description' => isset($this->subModal['description']) ? Filter::RemoveHtml($this->subModal['description']) : null,
                'image' => $url,
            ];

            Modal::create($data);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'subModal',
            ]);

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearAddFileInput');

            $this->closeAddModal();
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

    /**
     * show edit modal
     */
    public function showAddModal()
    {
        //get user with data
        $this->subModal = [];

        //show the modal
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        //close the modal
        $this->showAddModal = false;

        //empty user data
        $this->subModal = [];

        //reset validation messages
        $this->resetValidation();
    }
}
