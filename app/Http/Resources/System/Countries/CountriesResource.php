<?php

namespace App\Http\Resources\System\Countries;

use App\Http\Resources\System\Countries\Governorates\GovernoratesResource;
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

        $governorates = $this->governorates()
            ->orderBy('order')
            ->get();

        return [
            'id' => $this->id,
            'name' => $this->{$language_column},
            'code' => $this->code,
            'mobileCode' => $this->mobile_code,
            'governorates' => GovernoratesResource::collection($governorates),
        ];
    }
}
