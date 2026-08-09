<?php

namespace App\Http\Livewire\Interests;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Interests\Interest;
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

class InterestInquiryComponent extends LivewireDatatable
{
    use WithFileUploads;
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.interests.interest';
    public string $afterTableSlot2 = 'modals.interests.sub-interests.add';
    public $model = Interest::class;
    public array $interest;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showAddModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    private string $name_column = '';
    public bool $has_delete = true;
    public ?int $interest_id = null;
    public array $subInterest = [];

    protected $listeners = [
        'showAddModal'
    ];

    /**
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
                    <a class='text-center' target='_blank' href='" . route('files.image.get', $image) . "'>
                        <img src='" . route('files.image.get', $image) . "' class='rounded-circle' height='50' width='50'/>
                    </a>
                </div>" : '<div class="text-center">-</div>';
            })
                ->label(__('pages/interests/index.content.datatable.image')),
            Column::name('name_en')
                ->label(__('pages/interests/index.content.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name('name_ar')
                ->label(__('pages/interests/index.content.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_active')
                ->label(__('pages/interests/index.content.datatable.active'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.interests.table-actions', ['id' => $id]);
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
        return Interest::where('parent_interest_id', $this->interest_id);
    }

    /**
     * get admin language
     */
    public function getAdminLanguage()
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $this->name_column = 'name_ar';
        } else {
            $this->name_column = 'name_en';
        }
    }

    /**
     * show delete modal for selected rows, or a single interest by id
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

        $this->setDeleteModalTextsForSelection();
        $this->showDeleteModal = true;
    }

    /**
     * Set delete modal title/content based on selection count and interest name
     */
    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/interests/index.modal.delete';

        if (count($this->selected) === 1) {
            $interest = Interest::find($this->selected[0]);
            $name = '';
            if ($interest) {
                $name = App::currentLocale() === 'ar'
                    ? ($interest->name_ar ?: $interest->name_en)
                    : ($interest->name_en ?: $interest->name_ar);
            }

            $this->deleteModalTexts = [
                'title' => __("$base.title"),
                'content' => __("$base.content", ['name' => $name]),
                'cancel' => __("$base.cancel"),
                'submit' => __("$base.submit"),
            ];

            return;
        }

        $this->deleteModalTexts = [
            'title' => __("$base.title_multiple"),
            'content' => __("$base.content_multiple"),
            'cancel' => __("$base.cancel"),
            'submit' => __("$base.submit"),
        ];
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('interests.delete')) {
            //send toastr alert with error
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
            //get interests
            $interests = Interest::whereIn('id', $this->selected)
                ->withCount('childInterests')
                ->get();

            //block delete if an interest still has sub interests
            $inUse = $interests->first(function ($interest) {
                return $interest->child_interests_count > 0;
            });

            if ($inUse) {
                DB::rollBack();
                $this->alert('error', __('pages/interests/index.modal.delete.in_use'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
                $this->showDeleteModal = false;
                return null;
            }

            //delete data
            parent::delete($this->selected);

            //delete images belongs to these interests
            foreach ($interests as $interest) {
                if ($interest->image) {
                    Files::deleteS3File($interest->image);
                }
            }
            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'interests', [
                'interests' => $interests
            ], "Delete: interests");

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
        //get interest with data
        $this->interest = Interest::where('id', $id)
            ->first()
            ->toArray();

        $this->interest['is_active'] = $this->interest['is_active'] ? 1 : 0;
        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty interest data
        $this->interest = [];

        //reset validation messages
        $this->resetValidation();
    }


    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('interests.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'interest.name_en' => ['required', "unique:interests,name_en,$id"],
            'interest.name_ar' => ['required', "unique:interests,name_ar,$id"],
            'interest.description' => ['nullable'],
            'interest.new_image' => ['nullable'],
            'interest.is_active' => ['boolean'],
        ]);

        //set data
        $data = $this->interest;
        $data['name_en'] = Filter::RemoveHtml($this->interest['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->interest['name_ar']);
        $data['description'] = isset($this->interest['description']) ? Filter::RemoveHtml($this->interest['description']) : null;
        $data['is_active'] = $this->interest['is_active'];

        //unset the id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get interest
            $interest = Interest::findOrFail($id);

            //check if admin chosen image or not then upload it
            if (isset($this->interest['new_image']) && $this->interest['new_image'] != null) {
                //check if interest had a previous image
                if ($interest->image) {
                    Files::deleteS3File($interest->image);
                }
                $data['image'] = $this->interest['new_image']->store('uploads/interests', 'local');
            }

            //add log
            AdminLogs::log('edit', 'interests', [
                'old' => $interest,
                'new' => $data,
            ], "Edit: interest #$id");

            //update interest
            $interest->update($data);

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
            'title' => __('pages/interests/index.modal.delete.title'),
            'content' => __('pages/interests/index.modal.delete.content'),
            'cancel' => __('pages/interests/index.modal.delete.cancel'),
            'submit' => __('pages/interests/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/interests/index.modal.edit.title'),
            'cancel' => __('pages/interests/index.modal.edit.cancel'),
            'submit' => __('pages/interests/index.modal.edit.submit'),
        ];
    }

    /**
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('interests.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate([
            'interest_id' => ['required', "exists:interests,id"],
            'subInterest.name_en' => ['required', "unique:interests,name_en"],
            'subInterest.name_ar' => ['required', "unique:interests,name_ar"],
            'subInterest.description' => ['nullable'],
            'subInterest.interest_image' => ['nullable'],
        ]);


        DB::beginTransaction();
        try {
            if (isset($this->subInterest['interest_image'])) {
                $url = $this->subInterest['interest_image']->store('uploads/interests', 'local');
            } else {
                $url = null;
            }

            $data = [
                'parent_interest_id' => $this->interest_id,
                'name_en' => Filter::RemoveHtml($this->subInterest['name_en']),
                'name_ar' => Filter::RemoveHtml($this->subInterest['name_ar']),
                'description' => isset($this->subInterest['description']) ? Filter::RemoveHtml($this->subInterest['description']) : null,
                'image' => $url,
            ];

            Interest::create($data);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'subInterest',
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
        //get interest with data
        $this->subInterest = [];

        //show the modal
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        //close the modal
        $this->showAddModal = false;

        //empty interest data
        $this->subInterest = [];

        //reset validation messages
        $this->resetValidation();
    }
}
