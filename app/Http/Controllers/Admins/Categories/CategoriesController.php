<?php

namespace App\Http\Controllers\Admins\Categories;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use App\Models\Categories\Category;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('categories.inquiry')) {
            return abort(404);
        }
        return view('admin.pages.categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('categories.add')) {
            return abort(404);
        }
        return view('admin.pages.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the details of a category, its sub categories and the
     * advertisers affiliated to it.
     *
     * @param int $id
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('categories.inquiry')) {
            return abort(404);
        }

        //get the category with its parent
        $category = Category::with('parentCategory')
            ->findOrFail($id);

        //all the categories the details belong to: the category and its nested ones
        $tree_ids = $this->categoryTreeIds($category);

        //get the sub categories with their counters
        $sub_categories = Category::where('parent_category_id', $category->id)
            ->orderBy('order')
            ->get()
            ->each(function ($sub_category) {
                $ids = $this->categoryTreeIds($sub_category);

                $sub_category->advertisers_count = $this->advertisersCount($ids);
                $sub_category->offers_count = $this->offersCount($ids);
                $sub_category->posts_count = $this->postsCount($ids);
            });

        //set the statistics of the category
        $statistics = [
            'sub_categories' => $sub_categories->count(),
            'advertisers_total' => $this->advertisersCount($tree_ids),
            'advertisers_direct' => $this->advertisersCount([$category->id]),
            'advertisers_active' => $this->advertisersCount($tree_ids, function ($query) {
                $query->where('status', 'active');
            }),
            'advertisers_elite' => $this->advertisersCount($tree_ids, function ($query) {
                $query->where('is_elite', true);
            }),
            'offers' => $this->offersCount($tree_ids),
            'posts' => $this->postsCount($tree_ids),
        ];

        //Log Action
        AdminLogs::log('inquiry', 'categories', null, "Inquiry: category #$category->id details");

        return view('admin.pages.categories.show', [
            'category' => $category,
            'sub_categories' => $sub_categories,
            'statistics' => $statistics,
        ]);
    }

    /**
     * get the id of a category alongside the ids of all its nested categories
     * @param Category $category
     * @return array
     */
    private function categoryTreeIds(Category $category): array
    {
        $ids = [$category->id];
        $parents = [$category->id];

        //walk the tree level by level, the categories tree is shallow
        while (!empty($parents)) {
            $children = Category::whereIn('parent_category_id', $parents)
                ->whereNotIn('id', $ids)
                ->pluck('id')
                ->toArray();

            if (empty($children)) {
                break;
            }

            $ids = array_merge($ids, $children);
            $parents = $children;
        }

        return $ids;
    }

    /**
     * count the advertisers affiliated to any of the given categories
     * @param array $category_ids
     * @param callable|null $constraint
     * @return int
     */
    private function advertisersCount(array $category_ids, callable $constraint = null): int
    {
        $query = AdvertiserUser::whereIn('id', function ($query) use ($category_ids) {
            $query->select('advertiser_id')
                ->from('advertiser_categories')
                ->whereIn('category_id', $category_ids);
        });

        if ($constraint) {
            $constraint($query);
        }

        return $query->count();
    }

    /**
     * count the offers published under any of the given categories
     * @param array $category_ids
     * @return int
     */
    private function offersCount(array $category_ids): int
    {
        return DB::table('offers')
            ->whereIn('category_id', $category_ids)
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * count the advertisers posts published under any of the given categories
     * @param array $category_ids
     * @return int
     */
    private function postsCount(array $category_ids): int
    {
        return Post::whereIn('category_id', $category_ids)
            ->count();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }
}
