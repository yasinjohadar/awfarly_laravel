<?php

namespace App\Http\Resources\Shared\UsersBlockings;

use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Users\Customers\CustomersResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersBlockedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        if ($this->blocked->user_type === 'advertiser') {
            $user = AdvertisersResource::make($this->blocked)->resolve();
        } else {
            $user = CustomersResource::make($this->blocked)->resolve();
        }
        return $user;
    }
}
