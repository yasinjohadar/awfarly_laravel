<?php

namespace App\Http\Resources\Guests\Modal;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ModalCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'exists'   =>  isset($this->id) ? true : false,
            'data' => isset($this->id) ? [
                'id'    =>  $this->id ?? null,
                'title_ar'    =>  $this->title_ar ?? null,
                'title_en'    =>  $this->title_en ?? null,
                'body_ar'    =>  $this->body_ar ?? null,
                'body_en'    =>  $this->body_en ?? null,
                'link'    =>  $this->link ?? null,
                'start_at'    =>  $this->start_at ?? null,
                'end_at'    =>  $this->end_at ?? null,
                'recipients_type'    =>  $this->recipients_type ?? null,
            ] : null
        ];
    }
}
