<?php

namespace App\Http\Resources\System\Countries;

use App\Http\Resources\System\Countries\Cities\CitiesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CountriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get language column to show
        $language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        //get cities by its order
        $cities = $this->cities()
            ->orderBy('order')
            ->get();

        return [
            'id' => $this->id,
            'name' => $this->{$language_column},
            'code' => $this->code,
            'mobileCode' => $this->mobile_code,
            'cities' => CitiesResource::collection($cities),
        ];
    }
}
