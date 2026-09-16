<?php

namespace App\Helpers\Categories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The category hierarchy, loaded once and answered from memory.
 *
 * Every interest question in the app is a question about the shape of the
 * categories tree, and that tree is tiny (tens of rows) while the questions are
 * asked on every feed request and every notification fan-out. So the whole
 * (id => parent_id) edge list is cached and each lookup is a walk over arrays.
 * What this replaced issued one query per expansion, and in the case of the old
 * CategoriesFilter::categoryAndAncestorIds() one query per ancestor LEVEL.
 *
 * Cache invalidation is CategoryObserver (registered in AppServiceProvider); the
 * TTL is only a backstop for writes that bypass model events (raw SQL, seeders,
 * a DB console). Note that under a per-process cache driver (array, APCu) a
 * flush would only reach the current worker - the project runs file, which is
 * shared across workers.
 */
class CategoryTree
{
    /**
     * Versioned so that a change to the cached payload's shape can never be
     * served out of an entry a previous deploy warmed.
     */
    public const CACHE_KEY = 'categories.tree.v1';

    /** Backstop only - CategoryObserver is the real invalidation. */
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * Per-request memo on top of the cache store: Notifications and the admin
     * broadcast call these helpers repeatedly within a single request.
     *
     * @var array{parent: array<int, int|null>, children: array<int, int[]>}|null
     */
    private static ?array $map = null;

    /**
     * Strict descendants of $ids - the seeds themselves are NOT included.
     *
     * @param array $ids
     * @return int[]
     */
    public static function descendantIds(array $ids): array
    {
        $children = self::map()['children'];
        $seeds = self::normalize($ids);

        /// Seeding the visited set with the seeds is what makes a cycle safe:
        /// a child edge pointing back at a seed is never queued again.
        $visited = array_fill_keys($seeds, true);
        $queue = $seeds;
        $out = [];

        while ($queue) {
            $current = array_pop($queue);

            foreach ($children[$current] ?? [] as $child) {
                if (isset($visited[$child])) {
                    continue;
                }

                $visited[$child] = true;
                $out[] = $child;
                $queue[] = $child;
            }
        }

        return $out;
    }

    /**
     * Strict ancestors of $ids - the seeds themselves are NOT included.
     *
     * @param array $ids
     * @return int[]
     */
    public static function ancestorIds(array $ids): array
    {
        $parent = self::map()['parent'];
        $out = [];
        $seen = [];

        foreach (self::normalize($ids) as $seed) {
            $current = $parent[$seed] ?? null;

            /// Per-chain guard, so a cycle ends this walk without hiding an
            /// ancestor that a different seed legitimately reaches.
            $guard = [$seed => true];

            while ($current !== null && !isset($guard[$current])) {
                $guard[$current] = true;

                if (!isset($seen[$current])) {
                    $seen[$current] = true;
                    $out[] = $current;
                }

                $current = $parent[$current] ?? null;
            }
        }

        return $out;
    }

    /**
     * Self + all descendants - the BROWSE set. Tapping a category chip shows
     * everything filed under it, and nothing above it.
     *
     * @param array $ids
     * @return int[]
     */
    public static function subtreeIds(array $ids): array
    {
        $seeds = self::normalize($ids);

        if (empty($seeds)) {
            return [];
        }

        return array_values(array_unique(array_merge($seeds, self::descendantIds($seeds))));
    }

    /**
     * Self + descendants + ancestors - the INTEREST set.
     *
     * Content matches an interest iff the two lie on one root-to-leaf path:
     * either they are the same category, or one is an ancestor of the other.
     * Siblings never match, which is the whole point - picking "Web Development"
     * must not hand you "Mobile Development" just because both hang off
     * "Programming".
     *
     * Both walks start from $seeds and ONLY from $seeds. Expanding downward a
     * second time from the ancestors this just added is exactly what would leak
     * a sibling in (the ancestor of WebDev is Programming, and the descendants
     * of Programming include MobileDev), so the two walks stay independent.
     *
     * @param array $ids
     * @return int[]
     */
    public static function branchIds(array $ids): array
    {
        $seeds = self::normalize($ids);

        if (empty($seeds)) {
            return [];
        }

        return array_values(array_unique(array_merge(
            $seeds,
            self::descendantIds($seeds),
            self::ancestorIds($seeds)
        )));
    }

    /**
     * Drop the cached tree. Called by CategoryObserver on every category write,
     * and available in tinker after a write that bypassed model events.
     *
     * @return void
     */
    public static function flush(): void
    {
        self::$map = null;

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Inject an (id => parent_id) map directly, bypassing the database and the
     * cache, so the tree logic can be tested without a DB harness.
     *
     * @internal test seam
     * @param array<int, int|null> $parentMap
     * @return void
     */
    public static function fake(array $parentMap): void
    {
        self::$map = self::build($parentMap);
    }

    /**
     * @return array{parent: array<int, int|null>, children: array<int, int[]>}
     */
    private static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        self::$map = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, static function () {
            $parent = [];

            foreach (DB::table('categories')->select('id', 'parent_category_id')->get() as $row) {
                $parent[(int) $row->id] = $row->parent_category_id !== null
                    ? (int) $row->parent_category_id
                    : null;
            }

            return self::build($parent);
        });

        return self::$map;
    }

    /**
     * Turn a raw (id => parent_id) map into the parent/children pair the walks
     * use, repairing the two edges that would otherwise misbehave: a row that is
     * its own parent (a zero-length cycle), and a row whose parent no longer
     * exists. Both become roots rather than throwing or disappearing.
     *
     * Longer cycles are NOT repaired here - they are survivable at walk time,
     * and silently re-rooting one arbitrary member of a cycle would be a lie
     * about the data. See the guards in descendantIds()/ancestorIds().
     *
     * @param array<int, int|null> $parentMap
     * @return array{parent: array<int, int|null>, children: array<int, int[]>}
     */
    private static function build(array $parentMap): array
    {
        $parent = [];

        foreach ($parentMap as $id => $parentId) {
            $id = (int) $id;
            $parentId = $parentId !== null ? (int) $parentId : null;

            $parent[$id] = ($parentId === $id) ? null : $parentId;
        }

        $children = [];

        foreach ($parent as $id => $parentId) {
            if ($parentId === null) {
                continue;
            }

            if (!array_key_exists($parentId, $parent)) {
                $parent[$id] = null;

                continue;
            }

            $children[$parentId][] = $id;
        }

        return ['parent' => $parent, 'children' => $children];
    }

    /**
     * @param array $ids
     * @return int[] positive, unique, re-indexed
     */
    private static function normalize(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $ids))));
    }
}
