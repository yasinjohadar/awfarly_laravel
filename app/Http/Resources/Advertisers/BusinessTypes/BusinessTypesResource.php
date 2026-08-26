<?php

namespace App\Http\Resources\Advertisers\BusinessTypes;

use App;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessTypesResource extends JsonResource
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
            'isActive' => (bool)$this->is_active,
            'hasCategories' => (bool)$this->has_categories,
        ];
    }
}
