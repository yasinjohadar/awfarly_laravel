<?php

namespace App\Http\Livewire\Categories;

use App\Models\Categories\Category;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CategorySortComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    public ?int $category_id = null;
    public array $order = [];
    public string $language_column = 'name_ar';

    protected $listeners = [
        'showAddModal'
    ];

    /**
     * dispatch event to load scripts in the view
     */
    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    public function render()
    {
        $this->language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';
        if ($this->category_id) {
            $categories = Category::where('parent_category_id', $this->category_id)
                ->orderBy('order')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->{$this->language_column},
                        'id' => $category->id,
                    ];
                });
        } else {
            $categories = Category::whereNull('parent_category_id')
                ->orderBy('order')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->{$this->language_column},
                        'id' => $category->id,
                    ];
                });
        }
        return view('admin.pages.categories.sort', ['categories' => $categories]);
    }

    /**
     * set new order for files
     */
    public function setOrder($orders)
    {
        DB::beginTransaction();
        try {
            foreach ($orders as $index => $order) {
                Category::where('id', $order)
                    ->first()
                    ->update([
                        'order' => $index + 1
                    ]);
            }
            /*$this->dispatchBrowserEvent('getData');*/
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
