<?php

namespace App\Http\Resources\Users\Customers;

use App\Helpers\Files;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomersResource extends JsonResource
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

        //current user guard
        if (Auth::check() && Auth::guard(Auth::user()->token()->name . '-api')->check()) {
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
            $isSelf = ($this->id == $user->id && $this->user_type === $user->user_type);
        } else {
            $user = null;
            $isSelf = false;
        }
        //check whether this user is being followed or by the current logged user or not
        if ($user) {
            $followed = $user->followed()
                ->where('followed_type', CustomerUser::class)
                ->where('followed_id', $this->id)
                ->where('status', 'approved')
                ->first();

            //set is followed
            $is_followed = ($followed && $followed->status === 'approved');

            if ($followed) {
                $follow_status = $followed->status;
            } else {
                $follow_status = 'unfollowed';
            }

            if (($this->profile_privacy === 'private' || ($this->profile_privacy === 'followers' && !$is_followed) || in_array($this->status, ['inactive', 'banned'])) && !$isSelf) {
                $showNull = true;
            } else {
                $showNull = false;
            }
        } else {
            $is_followed = false;
            $follow_status = 'unfollowed';
            $showNull = true;
        }

        //get followers count
        $followers_count = $this->followers()
            ->where('status', 'approved')
            ->get()
            ->count();

        //get followed users count
        $followed_count = $this->followed()
            ->where('status', 'approved')
            ->count();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'type' => 'customer',
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'bio' => $this->bio ?? null,
            'country' => $this->country->{$language_column} ?? null,
            'governorate' => $this->governorate->{$language_column} ?? null,
            'city' => $this->city->{$language_column} ?? null,
            'countryCode' => $this->country_code ?? null,
            'governorateId' => $this->governorate_id ?? null,
            'cityId' => $this->city_id ?? null,
            'rating' => $this->rating ?? null,
            'socialAccounts' => [
                'contactNumber' => !$showNull ? ($this->contact_number ?? null) : null,
                'whatsappNumber' => !$showNull ? ($this->whatsapp_number ?? null) : null,
                'facebookUrl' => !$showNull ? ($this->facebook_url ?? null) : null,
                'twitterUrl' => !$showNull ? ($this->twitter_url ?? null) : null,
                'websiteUrl' => !$showNull ? ($this->website_url ?? null) : null,
            ],
            'isSelf' => $isSelf,
            'isFollowed' => $is_followed,
            'followStatus' => $follow_status,
            'chatStatus' => !$showNull ? ($this->chats_privacy) : null,
            'profilePrivacy' => $this->profile_privacy,
            'followersCount' => $followers_count ?? null,
            'followedCount' => $followed_count ?? null,
            'profileVisible' => !$showNull,
            'lastLoginAt' => !$showNull ? ($this->last_login_at ? Carbon::make($this->last_login_at)->diffForHumans() : null) : null,
            'isOnline' => $this->is_online,
            'addressLatitude' => (optional($this->location)->latitude ?? null),
            'addressLongitude' =>(optional($this->location)->longitude ?? null),
        ];
    }
}
