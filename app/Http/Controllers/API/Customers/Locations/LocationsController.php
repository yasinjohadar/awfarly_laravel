<?php

namespace App\Http\Controllers\API\Customers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\Countries\Cities\CitiesResource;
use App\Http\Resources\System\Countries\Governorates\GovernoratesResource;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocationsController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getUserLocations()
    {
        $user = Auth::guard('customer-api')->user();

        $governorateIds = $user->preferredGovernorates()->pluck('governorate_id')->toArray();
        $cityIds = $user->preferredCities()->pluck('city_id')->toArray();

        $governorates = Governorate::whereIn('id', $governorateIds)->orderBy('order')->get();
        $cities = City::whereIn('id', $cityIds)->orderBy('order')->get();

        return $this->apiResponse([
            'governorates' => GovernoratesResource::collection($governorates),
            'cities' => CitiesResource::collection($cities),
        ]);
    }

    /**
     * Replace-all preferred locations.
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addUserLocations(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $data = $request->all();

        $this->apiValidate($data, [
            'governorates' => ['nullable', 'array'],
            'governorates.*' => ['exists:governorates,id'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['exists:cities,id'],
        ]);

        $governorateIds = array_values(array_unique(array_map('intval', $data['governorates'] ?? [])));
        $cityIds = array_values(array_unique(array_map('intval', $data['cities'] ?? [])));

        if (!empty($cityIds)) {
            $cities = City::whereIn('id', $cityIds)->get(['id', 'governorate_id']);
            if ($cities->count() !== count($cityIds)) {
                return $this->apiBadRequestResponse(__('api/customers/locations/locations.invalid-city'));
            }

            // Drop cities covered by a selected whole governorate.
            $cityIds = $cities
                ->reject(function ($city) use ($governorateIds) {
                    return in_array((int) $city->governorate_id, $governorateIds, true);
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();
        }

        DB::beginTransaction();
        try {
            $user = Auth::guard('customer-api')->user();
            $user->preferredGovernorates()->delete();
            $user->preferredCities()->delete();

            foreach ($governorateIds as $governorateId) {
                $user->preferredGovernorates()->create([
                    'governorate_id' => $governorateId,
                ]);
            }

            foreach ($cityIds as $cityId) {
                $user->preferredCities()->create([
                    'city_id' => $cityId,
                ]);
            }

            $governorates = Governorate::whereIn('id', $governorateIds)->orderBy('order')->get();
            $cities = City::whereIn('id', $cityIds)->orderBy('order')->get();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/locations/locations.something-wrong'));
        }

        DB::commit();

        return $this->apiResponse([
            'message' => __('api/customers/locations/locations.added'),
            'data' => [
                'governorates' => GovernoratesResource::collection($governorates),
                'cities' => CitiesResource::collection($cities),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function deleteUserLocations(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $data = $request->all();

        $this->apiValidate($data, [
            'governorates' => ['nullable', 'array'],
            'governorates.*' => ['exists:governorates,id'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['exists:cities,id'],
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::guard('customer-api')->user();

            foreach (($data['governorates'] ?? []) as $governorateId) {
                $user->preferredGovernorates()
                    ->where('governorate_id', $governorateId)
                    ->delete();
            }

            foreach (($data['cities'] ?? []) as $cityId) {
                $user->preferredCities()
                    ->where('city_id', $cityId)
                    ->delete();
            }

            $governorateIds = $user->preferredGovernorates()->pluck('governorate_id')->toArray();
            $cityIds = $user->preferredCities()->pluck('city_id')->toArray();
            $governorates = Governorate::whereIn('id', $governorateIds)->orderBy('order')->get();
            $cities = City::whereIn('id', $cityIds)->orderBy('order')->get();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/locations/locations.something-wrong'));
        }

        DB::commit();

        return $this->apiResponse([
            'message' => __('api/customers/locations/locations.deleted'),
            'data' => [
                'governorates' => GovernoratesResource::collection($governorates),
                'cities' => CitiesResource::collection($cities),
            ],
        ]);
    }
}
