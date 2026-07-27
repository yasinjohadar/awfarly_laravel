<?php

namespace App\Http\Controllers\API\System\Countries\Cities;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\Countries\Cities\CitiesResource;
use App\Models\Countries\Cities\City;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CitiesController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getCities(Request $request)
    {
        $data = $request->only(['governorateId']);

        $this->apiValidate($data, [
            'governorateId' => 'nullable|exists:governorates,id',
        ]);

        $cities = City::query()
            ->when(
                !empty($data['governorateId']),
                fn ($query) => $query->where('governorate_id', $data['governorateId'])
            )
            ->orderBy('order')
            ->get();

        return $this->apiResponse(CitiesResource::collection($cities));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getCityById($id)
    {
        $city = City::where('id', $id)->first();

        if (!$city) {
            return $this->apiBadRequestResponse(__('api/system/countries/cities/cities.wrong-id'));
        }

        return $this->apiResponse(CitiesResource::make($city));
    }
}
