<?php

namespace App\Http\Resources\Users\Customers\Categories;

use App\Http\Resources\Categories\CategoriesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerCategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category' => CategoriesResource::make($this->category),
        ];
    }
}
