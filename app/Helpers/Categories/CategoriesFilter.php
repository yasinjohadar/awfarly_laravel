<?php

namespace App\Helpers\Categories;

use App\Models\Categories\Category;

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
     * @param mixed $user
     * @return int[]
     */
    public static function preferredCategoryIds($user): array
    {
        if (!$user || !method_exists($user, 'categories')) {
            return [];
        }

        return self::expandCategoryIds(
            $user->categories()->pluck('category_id')->toArray()
        );
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
}
