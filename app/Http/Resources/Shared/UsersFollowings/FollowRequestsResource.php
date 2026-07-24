<?php

namespace App\Http\Resources\Shared\UsersFollowings;

use App\Helpers\Files;
use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Users\Customers\CustomersResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class FollowRequestsResource extends JsonResource
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

        //get user rate
        $rate = $this->follower->rate ?? 0;

        return [
            'id' => $this->id,
            'owner' => [
                'id' => $this->follower->id,
                'username' => $this->follower->username,
                'bio' => $this->follower->bio ?? null,
                'name' => $this->follower->name,
                'businessTypeName' => $this->follower->business_type ? $this->follower->business->{$name_column} : null,
                'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
                'type' => $this->follower->user_type,
                'country' => $this->follower->country->{$name_column},
                'city' => $this->follower->city->{$name_column},
                'rate' => $rate,
                'isElite' => (bool)$this->follower->is_elite,
                'chatStatus' => $this->follower->chats_privacy,
                'profilePrivacy' => $this->follower->profile_privacy,
                'isSelf' => false,
                'isOnline' => $this->follower->is_online,
            ],
            'status' => $this->status,
            'createdAt' => Carbon::make($this->created_at)->diffForHumans(),
        ];
    }
}
