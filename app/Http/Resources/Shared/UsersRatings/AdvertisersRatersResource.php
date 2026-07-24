<?php

namespace App\Http\Resources\Shared\UsersRatings;

use App\Helpers\Files;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdvertisersRatersResource extends JsonResource
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

        //current user guard
        if (Auth::check() && Auth::guard(Auth::user()->token()->name . '-api')->check()) {
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
            $isSelf = ($this->user->id == $user->id && $this->user->user_type === $user->user_type);
            //check whether this user is being followed or by the current logged user or not
            $followed = $user
                ->followed()
                ->where('followed_type', $this->user->class)
                ->where('followed_id', $this->user_id)
                ->first();

            if ($followed) {
                $follow_status = $followed->status;
            } else {
                $follow_status = 'unfollowed';
            }
        } else {
            $user = null;
            $isSelf = false;
            $followed = false;
            $follow_status = 'unfollowed';
        }

        return [
            'id' => $this->id,
            'rate' => $this->rate,
            'comment' => $this->comment,
            'owner' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'bio' => $this->user->bio ?? null,
                'name' => $this->user->name,
                'businessTypeId' => $this->user->business_type,
                'businessTypeName' => $this->user->business_type ? $this->user->business->{$name_column} : null,
                'imageUrl' => route('files.image.get', $this->user->image) ?? null,
                'type' => $this->user->user_type,
                'country' => $this->user->country->{$name_column},
                'city' => $this->user->city->{$name_column},
                'rate' => $rate,
                'isElite' => (bool)$this->user->is_elite,
                'isFollowed' => (bool)$followed,
                'followStatus' => $follow_status,
                'isSelf' => $isSelf,
                'chatStatus' => $this->user->chats_privacy,
                'profilePrivacy' => $this->user->profile_privacy,
                'isOnline' => $this->user->is_online,
            ],
        ];
    }
}
