<?php

namespace App\Http\Resources\Categories;

use App\Helpers\Files;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class CategoriesResource extends JsonResource
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
            'id' => $this->category_id ?? $this->id,
            'name' => $this->{$language_column},
            'description' => $this->description ?? null,
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'subCategories' => CategoriesResource::collection($this->childCategories),
        ];
    }
}
