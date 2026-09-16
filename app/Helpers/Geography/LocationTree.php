<?php

namespace App\Helpers\Geography;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The governorate/city hierarchy, loaded once and answered from memory - the
 * location counterpart of App\Helpers\Categories\CategoryTree.
 *
 * Locations are a two-table hierarchy rather than a self-referencing one
 * (governorates, and cities carrying governorate_id), and it is fixed at two
 * levels by the schema, so this needs only the one edge: which governorate a
 * city belongs to, and the reverse. Everything else about the visibility rule
 * lives in Geography, because unlike a category - where content carries exactly
 * one id - a post carries a governorate AND a city at once, and that changes
 * what "the parent matches" is allowed to mean. See
 * Geography::applyPreferredPostLocationFilter().
 *
 * Invalidated by CityObserver and GovernorateObserver. The governorate one is
 * not redundant: cities.governorate_id cascades on delete, so removing a
 * governorate deletes its cities in the database without firing a single City
 * model event.
 */
class LocationTree
{
    /** Versioned so a change to the cached payload's shape cannot be served stale. */
    public const CACHE_KEY = 'locations.tree.v1';

    /** Backstop only - the observers are the real invalidation. */
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * @var array{governorateOfCity: array<int, int>, citiesOfGovernorate: array<int, int[]>}|null
     */
    private static ?array $map = null;

    /**
     * The governorates the given cities sit in.
     *
     * @param array $cityIds
     * @return int[]
     */
    public static function governorateIdsOfCities(array $cityIds): array
    {
        $governorateOfCity = self::map()['governorateOfCity'];
        $out = [];

        foreach (self::normalize($cityIds) as $cityId) {
            if (isset($governorateOfCity[$cityId])) {
                $out[] = $governorateOfCity[$cityId];
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Every city inside the given governorates.
     *
     * @param array $governorateIds
     * @return int[]
     */
    public static function cityIdsOfGovernorates(array $governorateIds): array
    {
        $citiesOfGovernorate = self::map()['citiesOfGovernorate'];
        $out = [];

        foreach (self::normalize($governorateIds) as $governorateId) {
            foreach ($citiesOfGovernorate[$governorateId] ?? [] as $cityId) {
                $out[] = $cityId;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Drop the cached tree. Called by the city/governorate observers, and
     * available in tinker after a write that bypassed model events.
     *
     * @return void
     */
    public static function flush(): void
    {
        self::$map = null;

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Inject a (city_id => governorate_id) map directly, bypassing the database
     * and the cache, so the rules can be tested without a DB harness.
     *
     * @internal test seam
     * @param array<int, int|null> $cityToGovernorate
     * @return void
     */
    public static function fake(array $cityToGovernorate): void
    {
        self::$map = self::build($cityToGovernorate);
    }

    /**
     * @return array{governorateOfCity: array<int, int>, citiesOfGovernorate: array<int, int[]>}
     */
    private static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        self::$map = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, static function () {
            $cityToGovernorate = [];

            foreach (DB::table('cities')->select('id', 'governorate_id')->get() as $row) {
                $cityToGovernorate[(int) $row->id] = $row->governorate_id !== null
                    ? (int) $row->governorate_id
                    : null;
            }

            return self::build($cityToGovernorate);
        });

        return self::$map;
    }

    /**
     * A city whose governorate is null or zero is simply left out of both
     * directions: it belongs to no governorate, so it can neither derive one nor
     * be reached through one. The column is NOT NULL in the schema, so this only
     * guards against a hand-edited row.
     *
     * @param array<int, int|null> $cityToGovernorate
     * @return array{governorateOfCity: array<int, int>, citiesOfGovernorate: array<int, int[]>}
     */
    private static function build(array $cityToGovernorate): array
    {
        $governorateOfCity = [];
        $citiesOfGovernorate = [];

        foreach ($cityToGovernorate as $cityId => $governorateId) {
            $cityId = (int) $cityId;
            $governorateId = (int) $governorateId;

            if ($cityId <= 0 || $governorateId <= 0) {
                continue;
            }

            $governorateOfCity[$cityId] = $governorateId;
            $citiesOfGovernorate[$governorateId][] = $cityId;
        }

        return [
            'governorateOfCity' => $governorateOfCity,
            'citiesOfGovernorate' => $citiesOfGovernorate,
        ];
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
