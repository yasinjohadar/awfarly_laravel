<?php

namespace App\Helpers\Categories;

use App\Models\Categories\Category;
use Illuminate\Support\Facades\DB;

class CategoriesFilter
{
    /**
     * Whether the client asked to bypass interest filtering.
     * Missing / null / empty → false (apply interests by default).
     */
    public static function wantsAllCategories(array $data): bool
    {
        if (!array_key_exists('isGetAllCategories', $data) || $data['isGetAllCategories'] === null || $data['isGetAllCategories'] === '') {
            return false;
        }

        $value = $data['isGetAllCategories'];

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Expand parent category ids to include their direct children.
     * Posts/offers are usually tagged with child categories while interests save parents.
     *
     * @param array $categoryIds
     * @return int[]
     */
    public static function expandCategoryIds(array $categoryIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $categoryIds))));

        if (empty($ids)) {
            return [];
        }

        $children = Category::whereIn('parent_category_id', $ids)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->toArray();

        return array_values(array_unique(array_merge($ids, $children)));
    }

    /**
     * The categories the VIEWER follows — their interests, never the categories
     * they publish under. Both user types expose interests() (on a customer it
     * aliases categories(), since a customer publishes nothing), so no branch
     * on the user type is needed here.
     *
     * @param mixed $user
     * @return int[]
     */
    public static function preferredCategoryIds($user): array
    {
        if (!$user || !method_exists($user, 'interests')) {
            return [];
        }

        return self::expandCategoryIds(
            $user->interests()->pluck('category_id')->toArray()
        );
    }

    /**
     * Replace the contents of a user↔category relation with exactly $categoryIds,
     * touching only the rows that actually changed.
     *
     * The previous pattern everywhere was `$relation->delete()` followed by
     * re-inserting. That destroyed and recreated every row, which reshuffled the
     * insertion order the default post/offer category depends on — so editing
     * one's categories silently changed the default category of every future
     * post. Syncing leaves untouched rows untouched.
     *
     * @param \Illuminate\Database\Eloquent\Relations\HasMany $relation
     * @param array $categoryIds
     * @return void
     */
    public static function syncCategories($relation, array $categoryIds): void
    {
        $wanted = array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $categoryIds))));

        $current = $relation->pluck('category_id')
            ->map(static fn ($id) => (int) $id)
            ->toArray();

        $toRemove = array_diff($current, $wanted);
        $toAdd = array_diff($wanted, $current);

        if (!empty($toRemove)) {
            $relation->whereIn('category_id', $toRemove)->delete();
        }

        foreach ($toAdd as $categoryId) {
            $relation->firstOrCreate(['category_id' => $categoryId]);
        }
    }

    /**
     * Apply interest categories (with children) when no explicit categoryId and not "get all".
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyFeedCategoryFilter($query, array $data, $user, string $column)
    {
        if (!empty($data['categoryId'])) {
            $ids = self::expandCategoryIds([(int) $data['categoryId']]);

            return $query->whereIn($column, $ids);
        }

        if (self::wantsAllCategories($data)) {
            return $query;
        }

        $ids = self::preferredCategoryIds($user);

        if (empty($ids)) {
            return $query;
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Match at the advertiser level: shows content from advertisers whose own selected
     * categories overlap with the user's selected categories. Advertisers whose business
     * type has no categories (e.g. "Shopper"/"متسوق") are exempt from this match — their
     * content still surfaces via the content-level filter alone.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyFeedAdvertiserCategoryFilter($query, array $data, $user, string $advertiserTable = 'advertisers_users')
    {
        $categoryLessBusinessTypeIds = DB::table('advertisers_business_types')
            ->where('has_categories', false)
            ->pluck('id');

        if (!empty($data['categoryId'])) {
            $ids = self::expandCategoryIds([(int) $data['categoryId']]);

            return $query->where(function ($q) use ($ids, $advertiserTable, $categoryLessBusinessTypeIds) {
                $q->whereIn("{$advertiserTable}.id", function ($sub) use ($ids) {
                    $sub->select('advertiser_id')->from('advertiser_categories')->whereIn('category_id', $ids);
                })->orWhereIn("{$advertiserTable}.business_type", $categoryLessBusinessTypeIds);
            });
        }

        if (self::wantsAllCategories($data)) {
            return $query;
        }

        $ids = self::preferredCategoryIds($user);

        if (empty($ids)) {
            return $query;
        }

        return $query->where(function ($q) use ($ids, $advertiserTable, $categoryLessBusinessTypeIds) {
            $q->whereIn("{$advertiserTable}.id", function ($sub) use ($ids) {
                $sub->select('advertiser_id')->from('advertiser_categories')->whereIn('category_id', $ids);
            })->orWhereIn("{$advertiserTable}.business_type", $categoryLessBusinessTypeIds);
        });
    }
}
