<?php

namespace App\Http\Controllers\API\System\Countries;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\Countries\CountriesResource;
use App\Models\Countries\Country;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class CountriesController extends Controller
{
    /**
     * get all countries
     * @return Application|ResponseFactory|Response
     */
    public function getCountries()
    {
        //countries
        $countries = Country::orderBy('order')
            ->where('is_active', true)
            ->whereHas('governorates')
            ->get();

        return $this->apiResponse(CountriesResource::collection($countries));
    }

    /**
     * get country by its code
     * @param $code
     * @return Application|ResponseFactory|Response
     */
    public function getCountryByCode($code)
    {
        //country
        $country = Country::where('code', $code)
            ->where('is_active', true)
            ->first();

        //return error if country wasn't found
        if (!$country) {
            return $this->apiBadRequestResponse(__('api/system/countries/countries.wrong-code'));
        }

        return $this->apiResponse(CountriesResource::make($country));
    }
}
