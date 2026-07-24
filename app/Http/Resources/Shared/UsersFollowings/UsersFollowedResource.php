<?php

namespace App\Http\Resources\Shared\UsersFollowings;

use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Users\Customers\CustomersResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersFollowedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        if ($this->followed->user_type === 'advertiser') {
            $user = AdvertisersResource::make($this->followed)->resolve();
        } else {
            $user = CustomersResource::make($this->followed)->resolve();
        }
        return $user;
    }
}
