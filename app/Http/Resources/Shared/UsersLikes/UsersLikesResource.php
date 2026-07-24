<?php

namespace App\Http\Resources\Shared\UsersLikes;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersLikesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->user->id,
            'username' => $this->user->username,
            'name' => $this->user->name,
            'imageUrl' => $this->user->image ? route('files.image.get', $this->user->image) : null,
            'bio' => $this->user->bio ?? null,
            'countryCode' => $this->user->country_code ?? null,
        ];
    }
}
