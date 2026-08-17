<?php

namespace App\Http\Livewire\Frontend\Categories;

use App\Helpers\Files;
use App\Helpers\Settings;
use App\Http\Livewire\Frontend\Home\HomeComponent;
use App\Models\Categories\Category;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Illuminate\Support\Collection;

class CategoriesComponent extends Component
{
    public ?Collection $categories = null;
    public ?int $category_id = null;
    public ?int $parent_category = null;

    protected $listeners = [
        'setCategoryId'
    ];

    public function mount()
    {
        $this->categories = Category::whereNull('parent_category_id')
            /*->whereHas('childCategories')*/
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                $name = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';
                if (!is_null($category->image) && !empty($category->image) && $category->image != null) {
                    $storagePath = route('files.image.get', $category->image);
                } else {
                    //Set default image
                    $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                }
                return [
                    'id' => $category->id,
                    'name' => $category->{$name},
                    'description' => $category->description,
                    'image' => $storagePath,
                ];
            });
    }

    public function render()
    {
        return view('livewire.frontend.categories.categories');
    }

    public function setCategoryId($id)
    {
        if (!$id) {
            $this->categories = Category::whereNull('parent_category_id')
                /*->whereHas('childCategories')*/
                ->orderBy('order')
                ->get()
                ->map(function ($category) {
                    $name = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';
                    if (!is_null($category->image) && !empty($category->image) && $category->image != null) {
                        $storagePath = route('files.image.get', $category->image);
                    } else {
                        //Set default image
                        $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                    }
                    return [
                        'id' => $category->id,
                        'name' => $category->{$name},
                        'description' => $category->description,
                        'image' => $storagePath,
                    ];
                });
            $this->parent_category = null;
        } else {
            $category = Category::where('id', $id)
                ->first();

            if ($category->childCategories()->count() > 0) {
                $this->parent_category = $this->category_id ?? $id;
                $this->categories = Category::where('id', $id)
                    ->get()
                    ->map(function ($category) {
                        $name = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';
                        if (!is_null($category->image) && !empty($category->image) && $category->image != null) {
                            $storagePath = route('files.image.get', $category->image);
                        } else {
                            //Set default image
                            $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                        }
                        return [
                            'id' => $category->id,
                            'name' => $category->{$name},
                            'description' => $category->description,
                            'image' => $storagePath,
                            'subCategories' => $category->childCategories()
                                ->orderBy('order')
                                ->get()
                                ->map(function ($children) {
                                    $name = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';
                                    if (!is_null($children->image) && !empty($children->image) && $children->image != null) {
                                        $storagePath = route('files.image.get', $children->image);
                                    } else {
                                        //Set default image
                                        $storagePath = Settings::Logo('assets/images/frontend/logo.png');
                                    }
                                    return [
                                        'id' => $children->id,
                                        'name' => $children->{$name},
                                        'description' => $children->description,
                                        'image' => $storagePath,
                                    ];
                                })
                        ];
                    });
            }
        }
        $this->category_id = $id;
        $this->emitTo('frontend.home.home-component', 'setCategoryId', $id);
    }
}
