<?php

namespace App\Http\Controllers\API\System\Countries\Cities;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\Countries\Cities\CitiesResource;
use App\Models\Countries\Cities\City;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class CitiesController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getCities()
    {
        //get all cities
        $cities = City::orderBy('order')
            ->get();

        return $this->apiResponse(CitiesResource::collection($cities));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getCityById($id)
    {
        //get city
        $city = City::where('id', $id)
            ->first();

        //if the city wasn't found return exception
        if (!$city) {
            return $this->apiBadRequestResponse(__('api/system/countries/cities/cities.wrong-id'));
        }

        return $this->apiResponse(CitiesResource::make($city));
    }
}
