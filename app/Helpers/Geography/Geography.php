<?php

namespace App\Helpers\Geography;

use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Geography
{
    public static function requiredLocationRules(): array
    {
        return [
            'governorateId' => 'required|exists:governorates,id',
            'cityId' => 'required|exists:cities,id',
        ];
    }

    public static function optionalLocationRules(): array
    {
        return [
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
        ];
    }

    public static function locationFilterFields(): array
    {
        return ['governorateId', 'cityId'];
    }

    public static function validateCityBelongsToGovernorate(array $data): ?string
    {
        if (empty($data['governorateId']) || empty($data['cityId'])) {
            return null;
        }

        $city = City::find($data['cityId']);

        if (!$city || (string) $city->governorate_id !== (string) $data['governorateId']) {
            return __('api/geography/geography.city-not-in-governorate');
        }

        return null;
    }

    /**
     * Derive the governorate from the city when a client sends only `cityId`.
     *
     * Without this a partial update writes `city_id` and leaves a stale
     * `governorate_id` behind (see assignUserLocation), which later makes
     * validateCityBelongsToGovernorate fail on every post the user tries to
     * add. Older app builds send exactly that shape.
     *
     * Call after `$request->only(...)` and BEFORE validation, so the derived id
     * is itself validated and seen by validateCityBelongsToGovernorate.
     *
     * @param array $data
     * @param Request|null $request
     * @return void
     */
    public static function fillGovernorateFromCity(array &$data, ?Request $request = null): void
    {
        if (!empty($data['governorateId']) || empty($data['cityId'])) {
            return;
        }

        $city = City::find($data['cityId']);

        if (!$city || !$city->governorate_id) {
            return;
        }

        $data['governorateId'] = $city->governorate_id;

        //keep the request in sync, the account controllers gate the assignment
        //on $request->has('governorateId')
        if ($request) {
            $request->merge(['governorateId' => $city->governorate_id]);
        }
    }

    /**
     * Filter posts by their own location, with fallback to advertiser location for legacy rows.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     */
    public static function applyPostLocationFilter($query, array $data, string $advertiserTable = 'advertisers_users')
    {
        if (!empty($data['cityId'])) {
            return $query->where(function ($q) use ($data, $advertiserTable) {
                $q->where('posts.city_id', $data['cityId'])
                    ->orWhere(function ($legacy) use ($data, $advertiserTable) {
                        $legacy->whereNull('posts.city_id')
                            ->where("{$advertiserTable}.city_id", $data['cityId']);
                    });
            });
        }

        if (!empty($data['governorateId'])) {
            return $query->where(function ($q) use ($data, $advertiserTable) {
                $q->where('posts.governorate_id', $data['governorateId'])
                    ->orWhere(function ($legacy) use ($data, $advertiserTable) {
                        $legacy->whereNull('posts.governorate_id')
                            ->where("{$advertiserTable}.governorate_id", $data['governorateId']);
                    });
            });
        }

        return $query;
    }

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $query
     */
    public static function applyUserLocationFilter($query, array $data, string $tablePrefix = 'advertisers_users')
    {
        if (!empty($data['cityId'])) {
            return $query->where("{$tablePrefix}.city_id", $data['cityId']);
        }

        if (!empty($data['governorateId'])) {
            return $query->where("{$tablePrefix}.governorate_id", $data['governorateId']);
        }

        return $query;
    }

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $query
     */
    public static function applyAdvertisementLocationFilter($query, array $data)
    {
        if (!empty($data['cityId'])) {
            return $query->where(function ($q) use ($data) {
                $q->whereJsonContains('cities', (string) $data['cityId'])
                    ->orWhereJsonContains('cities', (int) $data['cityId']);
            });
        }

        if (!empty($data['governorateId'])) {
            return $query->where(function ($q) use ($data) {
                $q->whereJsonContains('governorates', (string) $data['governorateId'])
                    ->orWhereJsonContains('governorates', (int) $data['governorateId']);
            });
        }

        if (!empty($data['countryCode'])) {
            $governorates = Governorate::where('country_code', $data['countryCode'])
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            return $query->where(function ($q) use ($governorates) {
                foreach ($governorates as $governorateId) {
                    $q->whereJsonContains('governorates', $governorateId);
                }
            });
        }

        return $query->where(function ($q) {
            $q->whereNull('governorates')
                ->whereNull('cities');
        });
    }

    public static function advertisementFilterRules(): array
    {
        return array_merge([
            'countryCode' => ['nullable', 'exists:countries,code'],
        ], self::optionalLocationRules());
    }

    public static function assignUserLocation($user, array $data): void
    {
        //belt and braces: even if the caller skipped fillGovernorateFromCity,
        //never let a city land next to a governorate that does not own it
        self::fillGovernorateFromCity($data);

        if (!empty($data['governorateId'])) {
            $user->governorate_id = $data['governorateId'];
        }

        if (!empty($data['cityId'])) {
            $user->city_id = $data['cityId'];
        }

        //mirror case: a governorate-only update must not strand a city that
        //belongs to a different governorate
        if (!empty($user->city_id) && !empty($user->governorate_id)) {
            $city = City::find($user->city_id);

            if ($city && (string) $city->governorate_id !== (string) $user->governorate_id) {
                $user->city_id = null;
            }
        }
    }

    /**
     * @param array $ids
     * @return int[] positive, unique, re-indexed
     */
    private static function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $ids))));
    }

    public static function hasExplicitLocationFilter(array $data): bool
    {
        return !empty($data['cityId']) || !empty($data['governorateId']);
    }

    /**
     * The viewer's saved location interests, plus the governorates those cities
     * imply.
     *
     * 'governorates' and 'cities' are what the user actually picked.
     * 'derivedGovernorates' are the governorates their chosen cities sit in, and
     * they are deliberately NOT merged into 'governorates', because the two mean
     * different things to a filter:
     *
     *   - a picked governorate matches anything inside it, city included;
     *   - a derived one may only match content that has no city of its own.
     *
     * Merging them would surface every sibling city: picking Douma would imply
     * Rif Dimashq, and a post in Harasta carries Rif Dimashq too. See
     * applyPreferredPostLocationFilter() for where that distinction is enforced.
     *
     * @param mixed $user
     * @return array{governorates: int[], cities: int[], derivedGovernorates: int[]}
     */
    public static function preferredLocationIds($user): array
    {
        if (!$user || !method_exists($user, 'preferredGovernorates') || !method_exists($user, 'preferredCities')) {
            return ['governorates' => [], 'cities' => [], 'derivedGovernorates' => []];
        }

        $governorateIds = $user->preferredGovernorates()
            ->pluck('governorate_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $cityIds = $user->preferredCities()
            ->pluck('city_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        return [
            'governorates' => $governorateIds,
            'cities' => $cityIds,
            //a governorate picked outright already matches more broadly, so keep
            //the two sets disjoint
            'derivedGovernorates' => array_values(array_diff(
                LocationTree::governorateIdsOfCities($cityIds),
                $governorateIds
            )),
        ];
    }

    /**
     * Hard-filter posts by the user's saved location interests (multi select).
     *
     * The rule is the one the category feed uses, read onto the two-level
     * governorate/city hierarchy: content is visible when its location and one of
     * the viewer's saved locations lie on the same vertical line, and two cities
     * in the same governorate are siblings that never match.
     *
     *   picked governorate -> everything inside it, whatever city
     *   picked city        -> that city, plus governorate-LEVEL content
     *   picked city        -> never a sibling city
     *
     * A post carries a governorate AND a city at once, which is what makes the
     * middle line delicate: the governorate implied by a picked city may only be
     * matched against content that has no city of its own, or every sibling city
     * rides along with it (a post in Harasta carries Rif Dimashq exactly like a
     * governorate-level one does). Hence the whereNull('posts.city_id') gate.
     *
     * Each branch also keeps its fallback for posts predating the location
     * columns, which stand in the advertiser's own location for whichever column
     * is null.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyPreferredPostLocationFilter($query, $user, string $advertiserTable = 'advertisers_users')
    {
        $prefs = self::preferredLocationIds($user);
        $governorateIds = $prefs['governorates'];
        $cityIds = $prefs['cities'];
        $derivedGovernorateIds = $prefs['derivedGovernorates'];

        if (empty($governorateIds) && empty($cityIds)) {
            return $query;
        }

        return $query->where(function ($q) use ($governorateIds, $cityIds, $derivedGovernorateIds, $advertiserTable) {
            if (!empty($cityIds)) {
                $q->orWhereIn('posts.city_id', $cityIds)
                    ->orWhere(function ($legacy) use ($cityIds, $advertiserTable) {
                        $legacy->whereNull('posts.city_id')
                            ->whereIn("{$advertiserTable}.city_id", $cityIds);
                    });
            }

            if (!empty($governorateIds)) {
                $q->orWhereIn('posts.governorate_id', $governorateIds)
                    ->orWhere(function ($legacy) use ($governorateIds, $advertiserTable) {
                        $legacy->whereNull('posts.governorate_id')
                            ->whereIn("{$advertiserTable}.governorate_id", $governorateIds);
                    });
            }

            //a picked city also reaches content filed at its governorate's LEVEL,
            //and only that.
            //
            //"Governorate level" has to mean the post has no EFFECTIVE city, not
            //merely an empty posts.city_id: the branches above resolve a null
            //posts.city_id to the advertiser's own city, so a post with a null
            //city published by an advertiser in a neighbouring town is content in
            //that town, not content addressed to the whole governorate. Matching
            //it here would hand a Douma follower everything out of Harasta - the
            //exact sibling leak this rule exists to prevent. So both cities must
            //be null before the derived governorate is allowed to match, and the
            //governorate itself then resolves the same way the branches above do.
            if (!empty($derivedGovernorateIds)) {
                $q->orWhere(function ($governorateLevel) use ($derivedGovernorateIds, $advertiserTable) {
                    $governorateLevel->whereNull('posts.city_id')
                        ->whereNull("{$advertiserTable}.city_id")
                        ->where(function ($scope) use ($derivedGovernorateIds, $advertiserTable) {
                            $scope->whereIn('posts.governorate_id', $derivedGovernorateIds)
                                ->orWhere(function ($legacy) use ($derivedGovernorateIds, $advertiserTable) {
                                    $legacy->whereNull('posts.governorate_id')
                                        ->whereIn("{$advertiserTable}.governorate_id", $derivedGovernorateIds);
                                });
                        });
                });
            }
        });
    }

    /**
     * Hard-filter offers/advertisers by the user's saved location interests.
     *
     * Same rule as applyPreferredPostLocationFilter(). Offers carry no location
     * of their own, so the advertiser's is the content's location here, and an
     * advertiser who recorded a governorate but no city is the governorate-level
     * case - the only thing a city-derived governorate is allowed to match.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyPreferredUserLocationFilter($query, $user, string $tablePrefix = 'advertisers_users')
    {
        $prefs = self::preferredLocationIds($user);
        $governorateIds = $prefs['governorates'];
        $cityIds = $prefs['cities'];
        $derivedGovernorateIds = $prefs['derivedGovernorates'];

        if (empty($governorateIds) && empty($cityIds)) {
            return $query;
        }

        return $query->where(function ($q) use ($governorateIds, $cityIds, $derivedGovernorateIds, $tablePrefix) {
            if (!empty($cityIds)) {
                $q->orWhereIn("{$tablePrefix}.city_id", $cityIds);
            }

            if (!empty($governorateIds)) {
                $q->orWhereIn("{$tablePrefix}.governorate_id", $governorateIds);
            }

            if (!empty($derivedGovernorateIds)) {
                $q->orWhere(function ($governorateLevel) use ($derivedGovernorateIds, $tablePrefix) {
                    $governorateLevel->whereNull("{$tablePrefix}.city_id")
                        ->whereIn("{$tablePrefix}.governorate_id", $derivedGovernorateIds);
                });
            }
        });
    }

    /**
     * Of $candidateIds, return those with no saved location preference at all
     * (treated as eligible by default) OR whose preferred governorate/city
     * matches $governorateId/$cityId. Scoped to $candidateIds only — never scans
     * the full preferred-location tables.
     *
     * @param \Illuminate\Support\Collection $candidateIds
     * @param string $preferredGovernorateModel
     * @param string $preferredCityModel
     * @param string $ownerColumn
     * @param int|null $governorateId
     * @param int|null $cityId
     * @return \Illuminate\Support\Collection
     */
    public static function candidatesInterestedInLocation(
        \Illuminate\Support\Collection $candidateIds,
        string $preferredGovernorateModel,
        string $preferredCityModel,
        string $ownerColumn,
        ?int $governorateId,
        ?int $cityId
    ): \Illuminate\Support\Collection {
        if ($candidateIds->isEmpty()) {
            return $candidateIds;
        }

        $withPrefs = $preferredGovernorateModel::whereIn($ownerColumn, $candidateIds)->pluck($ownerColumn)
            ->merge($preferredCityModel::whereIn($ownerColumn, $candidateIds)->pluck($ownerColumn))
            ->unique();

        $matching = collect();
        if ($governorateId) {
            $matching = $matching->merge(
                $preferredGovernorateModel::whereIn($ownerColumn, $candidateIds)->where('governorate_id', $governorateId)->pluck($ownerColumn)
            );
        }
        if ($cityId) {
            $matching = $matching->merge(
                $preferredCityModel::whereIn($ownerColumn, $candidateIds)->where('city_id', $cityId)->pluck($ownerColumn)
            );
        }

        //governorate-LEVEL content (no city of its own) also reaches whoever
        //follows a city inside it - the mirror of the feed's derived-governorate
        //branch, and gated on $cityId being null for the same reason: content
        //that names a city must never reach followers of a sibling city
        if ($governorateId && !$cityId) {
            $citiesInGovernorate = LocationTree::cityIdsOfGovernorates([$governorateId]);

            if (!empty($citiesInGovernorate)) {
                $matching = $matching->merge(
                    $preferredCityModel::whereIn($ownerColumn, $candidateIds)
                        ->whereIn('city_id', $citiesInGovernorate)
                        ->pluck($ownerColumn)
                );
            }
        }

        $withoutPrefs = $candidateIds->diff($withPrefs);

        return $matching->merge($withoutPrefs)->unique()->values();
    }

    /**
     * A selected governorate's own city ids: picking a governorate should also
     * match candidates whose preference is set at the (more specific) city level
     * within it.
     *
     * @param int[] $governorateIds
     * @return int[]
     */
    public static function expandGovernorateIdsToCities(array $governorateIds): array
    {
        return LocationTree::cityIdsOfGovernorates($governorateIds);
    }

    /**
     * Bulk variant of candidatesInterestedInLocation() for admin-driven targeting where
     * several governorates/cities may be selected at once. Of $candidateIds, returns those
     * with no saved location preference at all (eligible by default) OR whose preferred
     * governorate/city is in $governorateIds/$cityIds. Empty $governorateIds AND $cityIds
     * means no location constraint at all — everyone in $candidateIds passes through.
     *
     * @param \Illuminate\Support\Collection $candidateIds
     * @param string $preferredGovernorateModel
     * @param string $preferredCityModel
     * @param string $ownerColumn
     * @param int[] $governorateIds
     * @param int[] $cityIds
     * @return \Illuminate\Support\Collection
     */
    public static function candidatesInterestedInLocations(
        \Illuminate\Support\Collection $candidateIds,
        string $preferredGovernorateModel,
        string $preferredCityModel,
        string $ownerColumn,
        array $governorateIds,
        array $cityIds
    ): \Illuminate\Support\Collection {
        if ($candidateIds->isEmpty() || (empty($governorateIds) && empty($cityIds))) {
            return $candidateIds;
        }

        $withPrefs = $preferredGovernorateModel::whereIn($ownerColumn, $candidateIds)->pluck($ownerColumn)
            ->merge($preferredCityModel::whereIn($ownerColumn, $candidateIds)->pluck($ownerColumn))
            ->unique();

        //walk the hierarchy both ways, so the caller can pass exactly what was
        //selected: a chosen governorate reaches whoever follows one of its
        //cities, and a chosen city reaches whoever follows the governorate it
        //sits in. Admin targeting is explicit, so unlike the feed there is no
        //sibling hazard to guard against here.
        $matchGovernorateIds = array_values(array_unique(array_merge(
            self::normalizeIds($governorateIds),
            LocationTree::governorateIdsOfCities($cityIds)
        )));
        $matchCityIds = array_values(array_unique(array_merge(
            self::normalizeIds($cityIds),
            LocationTree::cityIdsOfGovernorates($governorateIds)
        )));

        $matching = collect();
        if (!empty($matchGovernorateIds)) {
            $matching = $matching->merge(
                $preferredGovernorateModel::whereIn($ownerColumn, $candidateIds)->whereIn('governorate_id', $matchGovernorateIds)->pluck($ownerColumn)
            );
        }
        if (!empty($matchCityIds)) {
            $matching = $matching->merge(
                $preferredCityModel::whereIn($ownerColumn, $candidateIds)->whereIn('city_id', $matchCityIds)->pluck($ownerColumn)
            );
        }

        $withoutPrefs = $candidateIds->diff($withPrefs);

        return $matching->merge($withoutPrefs)->unique()->values();
    }

    /**
     * Hard-filter ads by preferred locations; nationwide (null targeting) still included.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyPreferredAdvertisementLocationFilter($query, $user)
    {
        $prefs = self::preferredLocationIds($user);
        $governorateIds = $prefs['governorates'];
        $cityIds = $prefs['cities'];
        $derivedGovernorateIds = $prefs['derivedGovernorates'];

        if (empty($governorateIds) && empty($cityIds)) {
            return $query;
        }

        return $query->where(function ($q) use ($governorateIds, $cityIds, $derivedGovernorateIds) {
            $q->where(function ($nationwide) {
                $nationwide->whereNull('governorates')
                    ->whereNull('cities');
            });

            foreach ($cityIds as $cityId) {
                $q->orWhereJsonContains('cities', (string) $cityId)
                    ->orWhereJsonContains('cities', (int) $cityId);
            }

            foreach ($governorateIds as $governorateId) {
                $q->orWhereJsonContains('governorates', (string) $governorateId)
                    ->orWhereJsonContains('governorates', (int) $governorateId);
            }

            //an ad aimed at a governorate but at no city in particular is the
            //governorate-level case, and is the only ad a city-derived
            //governorate may match - an ad that names its cities must not reach
            //followers of a sibling city
            if (!empty($derivedGovernorateIds)) {
                $q->orWhere(function ($governorateLevel) use ($derivedGovernorateIds) {
                    $governorateLevel->where(function ($withoutCities) {
                        $withoutCities->whereNull('cities')
                            ->orWhereJsonLength('cities', 0);
                    })->where(function ($inGovernorate) use ($derivedGovernorateIds) {
                        foreach ($derivedGovernorateIds as $governorateId) {
                            $inGovernorate->orWhereJsonContains('governorates', (string) $governorateId)
                                ->orWhereJsonContains('governorates', (int) $governorateId);
                        }
                    });
                });
            }
        });
    }
}
