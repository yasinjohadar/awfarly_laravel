<?php

namespace App\Http\Livewire\Categories;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Categories\Category;
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

class CategoriesInquiryComponent extends LivewireDatatable
{
    use WithFileUploads;
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.categories.edit';
    public $model = Category::class;
    public array $category;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public Collection $categories;
    private string $name_column = '';
    public bool $has_delete = true;

    public $listeners = [
        'setParentCategoryEdit',
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

        $this->getAllCategories();

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
                    <a class='text-center' target='_blank' href='" . route('category.image.get', $image) . "'>
                        <img src='" . route('category.image.get', $image) . "' class='rounded-circle' height='50' width='50'/>
                    </a>
                </div>" : '<div class="text-center">-</div>';
            })
                ->label(__('pages/categories/index.content.datatable.image')),
            Column::name('name_en')
                ->label(__('pages/categories/index.content.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name('name_ar')
                ->label(__('pages/categories/index.content.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            /*Column::name('description')
                ->label(__('pages/categories/index.content.datatable.description'))
                ->filterable()
                ->searchable(),*/
            /* NumberColumn::callback(['id', 'created_at'], function ($id) {
                return Category::where('id', $id)
                    ->first()
                    ->childCategories()
                    ->count();
            })
                ->label(__('pages/categories/index.content.datatable.sub_categories_count'))
                ->filterable()
                ->searchable(),*/
            BooleanColumn::name('is_active')
                ->label(__('pages/categories/index.content.datatable.active'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.categories.table-actions', ['id' => $id, 'parent' => true]);
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
        return Category::whereNull('parent_category_id')
            ->with('childCategories');
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
        if (!Auth::guard('admin')->user()->can('categories.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get categories
            $categories = Category::whereIn('id', $this->selected)
                ->get();

            //delete data
            parent::delete($this->selected);

            //delete images belongs to these categories
            foreach ($categories as $category) {
                if ($category->image) {
                    Files::deleteS3File($category->image);
                }
            }
            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'categories', [
                'categories' => $categories
            ], "Delete: categories");

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
        $this->category = Category::where('id', $id)
            ->first()
            ->toArray();

        $this->category['is_active'] = $this->category['is_active'] ? 1 : 0;

        $this->categories = Category::where('id', '!=', $id)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category[$this->name_column],
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
        $this->category = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('categories.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'category.parent_category_id' => ['nullable', "exists:categories,id"],
            'category.name_en' => ['required', "unique:categories,name_en,$id"],
            'category.name_ar' => ['required', "unique:categories,name_ar,$id"],
            'category.description' => ['nullable'],
            'category.new_image' => ['nullable'],
            'category.is_active' => ['boolean'],
        ]);

        //set data
        $data = $this->category;
        $data['name_en'] = Filter::RemoveHtml($this->category['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->category['name_ar']);
        $data['description'] = isset($this->category['description']) ? Filter::RemoveHtml($this->category['description']) : null;
        $data['is_active'] = $this->category['is_active'];
        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $category = Category::findOrFail($id);

            //check if admin chosen image or not then upload it
            if (isset($this->category['new_image']) && $this->category['new_image'] != null) {
                //check if category had a previous image
                if ($category->image) {
                    Files::deleteS3File($category->image);
                }
                $data['image'] = $this->category['new_image']->store('uploads/categories', 'local');
            }

            //check if the parent category id is empty
            if (empty($data['parent_category_id'])) {
                $data['parent_category_id'] = null;
            }
            //add log
            AdminLogs::log('edit', 'categories', [
                'old' => $category,
                'new' => $data,
            ], "Edit: category #$id");

            //update user
            $category->update($data);

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
     * get all categories
     */
    public function getAllCategories()
    {
        $this->categories = Category::get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category[$this->name_column],
                ];
            });
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/categories/index.modal.delete.title'),
            'content' => __('pages/categories/index.modal.delete.content'),
            'cancel' => __('pages/categories/index.modal.delete.cancel'),
            'submit' => __('pages/categories/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/categories/index.modal.edit.title'),
            'cancel' => __('pages/categories/index.modal.edit.cancel'),
            'submit' => __('pages/categories/index.modal.edit.submit'),
        ];
    }

    /**
     * set the value of parent category id once it changes
     * @param $parent_category_id
     */
    public function setParentCategoryEdit($parent_category_id)
    {
        $this->category['parent_category_id'] = $parent_category_id;

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
