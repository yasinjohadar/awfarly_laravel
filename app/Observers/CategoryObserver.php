<?php

namespace App\Observers;

use App\Helpers\Categories\CategoryTree;
use App\Models\Categories\Category;

class CategoryObserver
{
    /**
     * Drop the cached category tree whenever a category is created or updated.
     *
     * Interest filtering reads the whole hierarchy out of one cache entry
     * (CategoryTree), so re-parenting or adding a category has to invalidate it
     * or feeds keep answering from the old shape until the TTL expires.
     *
     * saved() rather than created() + updated() because both need the exact
     * same treatment, and re-parenting - the write that actually changes the
     * tree - is an update.
     *
     * @param Category $category
     * @return void
     */
    public function saved(Category $category): void
    {
        CategoryTree::flush();
    }

    /**
     * @param Category $category
     * @return void
     */
    public function deleted(Category $category): void
    {
        CategoryTree::flush();
    }

    /**
     * @param Category $category
     * @return void
     */
    public function restored(Category $category): void
    {
        CategoryTree::flush();
    }
}
