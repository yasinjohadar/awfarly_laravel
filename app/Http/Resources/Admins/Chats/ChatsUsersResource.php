<?php

namespace App\Http\Resources\Admins\Chats;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class ChatsUsersResource extends JsonResource
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
            'type' => $this->user_type,
            'name' => $this->name,
            'username' => $this->username,
            'imageUrl' => route('users.profile.image', $this->image),
            'country' => $this->country->{$language_column} ?? null,
            'city' => $this->city->{$language_column} ?? null,
            'isOnline' => $this->is_online,
        ];
    }
}
