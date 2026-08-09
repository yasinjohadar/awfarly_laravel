<?php

namespace App\Http\Livewire\Interests;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Interests\Interest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

class InterestsInquiryComponent extends LivewireDatatable
{
    use WithFileUploads;
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.interests.edit';
    public $model = Interest::class;
    public array $interest;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public Collection $interests;
    private string $name_column = '';
    public bool $has_delete = true;

    public $listeners = [
        'setParentInterestEdit',
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

        $this->getAllInterests();

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
                return view('admin.pages.interests.table-actions', ['id' => $id, 'parent' => true]);
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
        return Interest::whereNull('parent_interest_id')
            ->with('childInterests');
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
        //get interest with data
        $this->interest = Interest::where('id', $id)
            ->first()
            ->toArray();

        $this->interest['is_active'] = $this->interest['is_active'] ? 1 : 0;

        $this->interests = Interest::where('id', '!=', $id)
            ->get()
            ->map(function ($interest) {
                return [
                    'id' => $interest->id,
                    'name' => $interest[$this->name_column],
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
            'interest.parent_interest_id' => ['nullable', "exists:interests,id"],
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

            //check if the parent interest id is empty
            if (empty($data['parent_interest_id'])) {
                $data['parent_interest_id'] = null;
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
     * get all interests
     */
    public function getAllInterests()
    {
        $this->interests = Interest::get()
            ->map(function ($interest) {
                return [
                    'id' => $interest->id,
                    'name' => $interest[$this->name_column],
                ];
            });
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
     * set the value of parent interest id once it changes
     * @param $parent_interest_id
     */
    public function setParentInterestEdit($parent_interest_id)
    {
        $this->interest['parent_interest_id'] = $parent_interest_id;

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
