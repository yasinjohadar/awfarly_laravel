<?php

namespace App\Http\Resources\Customers\HiddenAdvertisers;

use App\Helpers\Files;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HiddenAdvertisersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get name column to show countries, cities in current user language
        $name_column = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';

        //get user rate
        $rate = $this->user->rate ?? 0;

        //check if current user is the owner of the post
        $isSelf = ($this->user->id == Auth::guard('customer-api')->id() && $this->user->user_type === 'advertiser');

        //check whether this user is being followed or by the current logged user or not
        $followed = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('followed_id', $this->advertiser_id)
            ->first();

        //set the follow status
        $followed_status = $followed ? $followed->status : __('api/advertisers/community/posts/posts.user-unfollowed');

        return [
            'id'=>$this->id,
            'advertiser' => [
                'id' => $this->advertiser->id,
                'username' => $this->advertiser->username,
                'bio' => $this->advertiser->bio ?? null,
                'name' => $this->advertiser->name,
                'imageUrl' => route('files.image.get', $this->image) ?? null,
                'type' => $this->advertiser->user_type,
                'country' => $this->advertiser->country->{$name_column},
                'city' => $this->advertiser->city->{$name_column},
                'rate' => $rate,
                'isElite' => (bool)$this->advertiser->is_elite,
                'followedStatus' => $followed_status,
                'isSelf' => $isSelf,
                'chatStatus' => $this->advertiser->chats_privacy,
                'profilePrivacy' => $this->advertiser->profile_privacy,
                'isOnline' => $this->advertiser->is_online,
            ],
        ];
    }
}
