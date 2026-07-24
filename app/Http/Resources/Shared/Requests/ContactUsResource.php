<?php

namespace App\Http\Resources\Shared\Requests;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactUsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => __("pages/requests/contact-us/inquiry.content.types.$this->type"),
            'name' => $this->name,
            'mobile' => $this->mobile,
            'whatsappMobile' => $this->whatsappMobile,
            'email' => $this->email,
            'message' => $this->message,
        ];
    }
}
