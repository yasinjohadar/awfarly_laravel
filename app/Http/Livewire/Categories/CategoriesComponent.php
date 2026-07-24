<?php

namespace App\Http\Livewire\Categories;

use App\Models\Categories\Category;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CategoriesComponent extends Component
{
    use LivewireAlert;

    private ?string $category_id = null;
    private ?Category $category = null;
    private bool $order = false;

    protected $listeners = [
        'setCategoryId' => 'setCategory',
    ];

    public function render()
    {
        if ($this->category_id) {
            $this->category = Category::where('id', $this->category_id)
                ->first();
        }
        return view('admin.pages.categories.inquiry', ['category' => $this->category ?? null, 'order' => $this->order ?? false]);
    }

    /**
     * @param null $category_id
     * @param bool $order
     */
    public function setCategory($category_id = null, bool $order = false)
    {
        if (!$category_id) {
            $this->category_id = null;
        } else {
            $this->category_id = $category_id;
        }
        $this->order = $order;
    }
}
