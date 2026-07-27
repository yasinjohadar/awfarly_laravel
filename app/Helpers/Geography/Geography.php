<?php

namespace App\Helpers\Geography;

use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Database\Eloquent\Builder;

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
        if (!empty($data['governorateId'])) {
            $user->governorate_id = $data['governorateId'];
        }

        if (!empty($data['cityId'])) {
            $user->city_id = $data['cityId'];
        }
    }
}
