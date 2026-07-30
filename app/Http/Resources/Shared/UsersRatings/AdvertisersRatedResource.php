<?php

namespace App\Http\Resources\Shared\UsersRatings;

use App\Helpers\Files;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdvertisersRatedResource extends JsonResource
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
        $rate = $this->advertiser->rate ?? 0;

        //current user guard
        if (Auth::check() && Auth::guard(Auth::user()->token()->name . '-api')->check()) {
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
            $isSelf = ($this->id == $user->id && $this->user_type === $user->user_type);
            $followed = $user->followed()
                ->where('followed_type', AdvertiserUser::class)
                ->where('followed_id', $this->advertiser_id)
                ->first();

            //set is followed
            $is_followed = ($followed && $followed->status === 'approved');
            if ($followed) {
                $follow_status = $followed->status;
            } else {
                $follow_status = 'unfollowed';
            }
        } else {
            $user = null;
            $isSelf = false;
            $is_followed = false;
            $follow_status = 'unfollowed';
        }

        return [
            'id' => $this->id,
            'rate' => $this->rate,
            'comment' => $this->comment,
            'owner' => [
                'id' => $this->advertiser->id,
                'username' => $this->advertiser->advertisername,
                'bio' => $this->advertiser->bio ?? null,
                'name' => $this->advertiser->name,
                'businessTypeId' => $this->advertiser->business_type,
                'businessTypeName' => $this->advertiser->business_type ? $this->advertiser->business->{$name_column} : null,
                'imageUrl' => route('files.image.get', $this->advertiser->image) ?? null,
                'type' => $this->advertiser->advertiser_type,
                'country' => optional($this->advertiser->country)->{$name_column},
                'governorate' => optional($this->advertiser->governorate)->{$name_column},
                'city' => optional($this->advertiser->city)->{$name_column},
                'governorateId' => $this->advertiser->governorate_id,
                'rate' => $rate,
                'isElite' => (bool)$this->advertiser->is_elite,
                'isFollowed' => $is_followed,
                'followStatus' => $follow_status,
                'isSelf' => $isSelf,
                'chatStatus' => $this->advertiser->chats_privacy,
                'profilePrivacy' => $this->advertiser->profile_privacy,
                'isOnline' => $this->advertiser->is_online,
            ],
        ];
    }
}
