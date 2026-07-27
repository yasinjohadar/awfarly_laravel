<?php

namespace App\Http\Resources\System\Countries\Governorates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class GovernoratesResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        return [
            'id' => $this->id,
            'name' => $this->{$language_column},
            'countryCode' => $this->country_code,
        ];
    }
}
