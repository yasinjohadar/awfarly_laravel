<?php

namespace App\Http\Resources\Customers\Chats;

use App\Helpers\Files;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

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
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        if ($this->user_type === 'advertiser') {
            $is_elite = (bool)$this->is_elite;
        } else {
            $is_elite = false;
        }
        return [
            'id' => $this->id,
            'type' => $this->user_type,
            'name' => $this->name,
            'username' => $this->username,
            'businessTypeName' => $this->business_type ? $this->business->{$name_column} : null,
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'country' => $this->country->{$name_column} ?? null,
            'city' => $this->city->{$name_column} ?? null,
            'isElite' => $is_elite,
            'isOnline' => $this->is_online,
        ];
    }
}
