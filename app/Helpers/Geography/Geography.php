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

    public static function hasExplicitLocationFilter(array $data): bool
    {
        return !empty($data['cityId']) || !empty($data['governorateId']);
    }

    /**
     * @param mixed $user
     * @return array{governorates: int[], cities: int[]}
     */
    public static function preferredLocationIds($user): array
    {
        if (!$user || !method_exists($user, 'preferredGovernorates') || !method_exists($user, 'preferredCities')) {
            return ['governorates' => [], 'cities' => []];
        }

        return [
            'governorates' => $user->preferredGovernorates()
                ->pluck('governorate_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray(),
            'cities' => $user->preferredCities()
                ->pluck('city_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Hard-filter posts by the user's saved location interests (multi select).
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyPreferredPostLocationFilter($query, $user, string $advertiserTable = 'advertisers_users')
    {
        $prefs = self::preferredLocationIds($user);
        $governorateIds = $prefs['governorates'];
        $cityIds = $prefs['cities'];

        if (empty($governorateIds) && empty($cityIds)) {
            return $query;
        }

        return $query->where(function ($q) use ($governorateIds, $cityIds, $advertiserTable) {
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
        });
    }

    /**
     * Hard-filter offers/advertisers by the user's saved location interests.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyPreferredUserLocationFilter($query, $user, string $tablePrefix = 'advertisers_users')
    {
        $prefs = self::preferredLocationIds($user);
        $governorateIds = $prefs['governorates'];
        $cityIds = $prefs['cities'];

        if (empty($governorateIds) && empty($cityIds)) {
            return $query;
        }

        return $query->where(function ($q) use ($governorateIds, $cityIds, $tablePrefix) {
            if (!empty($cityIds)) {
                $q->orWhereIn("{$tablePrefix}.city_id", $cityIds);
            }

            if (!empty($governorateIds)) {
                $q->orWhereIn("{$tablePrefix}.governorate_id", $governorateIds);
            }
        });
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

        if (empty($governorateIds) && empty($cityIds)) {
            return $query;
        }

        return $query->where(function ($q) use ($governorateIds, $cityIds) {
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
        });
    }
}
