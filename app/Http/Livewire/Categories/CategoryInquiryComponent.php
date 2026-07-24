<?php

namespace App\Http\Livewire\Categories;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Categories\Category;
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

class CategoryInquiryComponent extends LivewireDatatable
{
    use WithFileUploads;
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
   // public $afterTableSlot = 'modals.categories.category';
    //public string $afterTableSlot2 = 'modals.categories.sub-categories.add';
    public $model = Category::class;
    public array $category;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showAddModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    private string $name_column = '';
    public bool $has_delete = true;
    public ?int $category_id = null;
    public array $subCategory = [];

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
            BooleanColumn::name('is_active')
                ->label(__('pages/categories/index.content.datatable.active'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.categories.table-actions', ['id' => $id]);
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
        return Category::where('parent_category_id', $this->category_id);
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
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('categories.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate([
            'category_id' => ['required', "exists:categories,id"],
            'subCategory.name_en' => ['required', "unique:categories,name_en"],
            'subCategory.name_ar' => ['required', "unique:categories,name_ar"],
            'subCategory.description' => ['nullable'],
            'subCategory.category_image' => ['nullable'],
        ]);


        DB::beginTransaction();
        try {
            if (isset($this->subCategory['category_image'])) {
                $url = $this->subCategory['category_image']->store('uploads/categories', 'local');
            } else {
                $url = null;
            }

            $data = [
                'parent_category_id' => $this->category_id,
                'name_en' => Filter::RemoveHtml($this->subCategory['name_en']),
                'name_ar' => Filter::RemoveHtml($this->subCategory['name_ar']),
                'description' => isset($this->subCategory['description']) ? Filter::RemoveHtml($this->subCategory['description']) : null,
                'image' => $url,
            ];

            Category::create($data);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'subCategory',
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
        $this->subCategory = [];

        //show the modal
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        //close the modal
        $this->showAddModal = false;

        //empty user data
        $this->subCategory = [];

        //reset validation messages
        $this->resetValidation();
    }
}
