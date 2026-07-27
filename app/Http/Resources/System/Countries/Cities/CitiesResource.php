<?php

namespace App\Http\Resources\System\Countries\Cities;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CitiesResource extends JsonResource
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

        return [
            'id' => $this->id,
            'name' => $this->{$language_column},
            'governorateId' => $this->governorate_id,
        ];
    }
}
