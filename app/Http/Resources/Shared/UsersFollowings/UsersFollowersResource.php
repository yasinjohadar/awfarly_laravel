<?php

namespace App\Http\Resources\Shared\UsersFollowings;

use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Users\Customers\CustomersResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersFollowersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        if ($this->follower->user_type === 'advertiser') {
            $user = AdvertisersResource::make($this->follower)->resolve();
        } else {
            $user = CustomersResource::make($this->follower)->resolve();
        }
        return $user;
    }
}
