<?php

namespace App\Http\Resources\Interests;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class InterestsResource extends JsonResource
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
            'id' => $this->interest_id ?? $this->id,
            'name' => $this->{$language_column},
            'description' => $this->description ?? null,
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'subInterests' => InterestsResource::collection($this->childInterests),
        ];
    }
}
