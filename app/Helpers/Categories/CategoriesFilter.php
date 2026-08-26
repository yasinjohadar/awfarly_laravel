<?php

namespace App\Helpers\Categories;

use App\Models\Categories\Category;
use App\Models\Users\Advertisers\AdvertiserUser;
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
     * @param \Closure|null $includeOwn Optional extra condition OR'd into the interests
     *   filter (e.g. "this row belongs to the viewer") so the viewer's own content stays
     *   visible on their own feed even when its category isn't among their interests.
     */
    public static function applyFeedCategoryFilter($query, array $data, $user, string $column, ?\Closure $includeOwn = null)
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

        if ($includeOwn) {
            return $query->where(function ($q) use ($column, $ids, $includeOwn) {
                $q->whereIn($column, $ids)->orWhere($includeOwn);
            });
        }

        return $query->whereIn($column, $ids);
    }

    /**
     * Match at the advertiser level: shows advertisers whose own selected categories
     * overlap with the given categories, OR who have published approved content
     * (a post or a live offer) under one of them — an advertiser can post outside
     * their declared categories (see the Advertisers post/offer create validation,
     * which allows any category id), so the Advertisers tab must recognize that
     * content too or it silently disagrees with what the Posts/Offers tab shows for
     * the same category. Advertisers whose business type has no categories (e.g.
     * "Shopper"/"متسوق") are exempt from this match — their content still surfaces
     * via the content-level filter alone.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyFeedAdvertiserCategoryFilter($query, array $data, $user, string $advertiserTable = 'advertisers_users')
    {
        if (!empty($data['categoryId'])) {
            $ids = self::expandCategoryIds([(int) $data['categoryId']]);

            return $query->where(function ($q) use ($ids, $advertiserTable) {
                self::addAdvertiserCategoryMatch($q, $ids, $advertiserTable);
            });
        }

        if (self::wantsAllCategories($data)) {
            return $query;
        }

        $ids = self::preferredCategoryIds($user);

        if (empty($ids)) {
            return $query;
        }

        return $query->where(function ($q) use ($ids, $advertiserTable) {
            self::addAdvertiserCategoryMatch($q, $ids, $advertiserTable);
        });
    }

    /**
     * Add the "advertiser matches these category ids" OR-conditions to $q, covering
     * a declared advertiser category, a category-less business type, or approved
     * content (post/offer) published under one of the ids.
     *
     * @param \Illuminate\Database\Query\Builder $q
     * @param int[] $ids
     */
    private static function addAdvertiserCategoryMatch($q, array $ids, string $advertiserTable): void
    {
        $categoryLessBusinessTypeIds = DB::table('advertisers_business_types')
            ->where('has_categories', false)
            ->pluck('id');

        $q->whereIn("{$advertiserTable}.id", function ($sub) use ($ids) {
            $sub->select('advertiser_id')->from('advertiser_categories')->whereIn('category_id', $ids);
        })
            ->orWhereIn("{$advertiserTable}.business_type", $categoryLessBusinessTypeIds)
            ->orWhereIn("{$advertiserTable}.id", function ($sub) use ($ids) {
                $sub->select('user_id')->from('posts')
                    ->whereIn('category_id', $ids)
                    ->where('status', 'approved')
                    ->where('user_type', AdvertiserUser::class);
            })
            ->orWhereIn("{$advertiserTable}.id", function ($sub) use ($ids) {
                $sub->select('advertiser_id')->from('offers')
                    ->whereIn('category_id', $ids)
                    ->where('status', 'approved')
                    ->where('expires_at', '>', now());
            });
    }
}
