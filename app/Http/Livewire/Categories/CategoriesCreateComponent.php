<?php

namespace App\Http\Livewire\Categories;

use App\Helpers\Filter;
use App\Models\Categories\Category;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CategoriesCreateComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    public ?string $parent_category_id = null;
    public ?string $name_en = null;
    public ?string $name_ar = null;
    public ?string $description = null;
    public $image;
    public string $name_column;

    public $listeners = ['setParentCategoryCreate'];

    /**
     * CategoriesCreateComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->getAdminLanguage();
        parent::__construct($id);
    }

    /**
     * @var array
     */
    protected array $rules = [
        'parent_category_id' => ['nullable', "exists:categories,id"],
        'name_en' => ['required', "unique:categories,name_en"],
        'name_ar' => ['required', "unique:categories,name_ar"],
        'description' => ['nullable'],
        'image' => ['nullable'],
    ];

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get all categories
        $categories = Category::get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category[$this->name_column],
                ];
            });

        return view('livewire.pages.categories.create', [
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('categories.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate();
        DB::beginTransaction();
        try {
            if ($this->image != null) {
                $url = $this->image->store('uploads/categories', 'local');
            } else {
                $url = null;
            }
            //set parent id
            $parent = ($this->parent_category_id === '') ? null : $this->parent_category_id;

            $data = [
                'parent_category_id' => $parent,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
                'description' => isset($this->description) ? Filter::RemoveHtml($this->description) : null,
                'image' => $url,
            ];
            Category::create($data);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'parent_category_id',
                'name_en',
                'name_ar',
                'description',
                'image',
            ]);

            //dispatch event to refresh select2
            $this->dispatchBrowserEvent('refreshSelect2Create');

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput');
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

    public function getAdminLanguage()
    {
        $name_column = Auth::guard('admin')->user()->language_code;
        if ($name_column === 'ar') {
            $this->name_column = 'name_ar';
        } else {
            $this->name_column = 'name_en';
        }
    }

    /**
     * set the value of parent category id once it changes
     * @param $parent_category_id
     */
    public function setParentCategoryCreate($parent_category_id)
    {
        $this->parent_category_id = $parent_category_id;

        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }

    /**
     * dispatch select2 modal while updating
     */
    public function updating()
    {
        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }
}
