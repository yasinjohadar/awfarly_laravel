<?php

namespace App\Http\Resources\Users\Advertisers;

use App\Helpers\Files;
use App\Models\Users\Advertisers\AdvertiserUser;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class AdvertisersResource extends JsonResource
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
                ->where('followed_type', AdvertiserUser::class)
                ->where('followed_id', $this->id)
                ->first();

            //set is followed
            $is_followed = ($followed && $followed->status === 'approved');
            if ($followed) {
                $follow_status = $followed->status;
            } else {
                $follow_status = 'unfollowed';
            }
            //check whether user has hid this advertiser's posts or not
            $is_posts = $user->hiddenAdvertisers()
                ->where('advertiser_id', $this->id)
                ->exists();

            $isRated = $user->advertisersRated()
                ->where('advertiser_id', $this->id)
                ->exists();

            if (($this->profile_privacy === 'private' || ($this->profile_privacy === 'followers' && !$is_followed) || in_array($this->status, ['inactive', 'banned'])) && !$isSelf) {
                $showNull = true;
            } else {
                $showNull = false;
            }
        } else {
            $is_followed = false;
            $follow_status = 'unfollowed';
            $is_posts = false;
            $isRated = false;
            $showNull = true;
        }

        //get followers count
        $followers_count = $this->followers()
            ->get()
            ->count();

        //get followed users count
        $followed_count = $this->followed()
            ->get()
            ->count();
        return [
            'id' => $this->id,
            'businessTypeId' => $this->business->id ?? null,
            'businessTypeName' => $this->business->{$language_column} ?? null,
            'username' => $this->username,
            'name' => $this->name,
            'type' => 'advertiser',
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'bio' => $this->bio ?? null,
            'country' => $this->country->{$language_column} ?? null,
            'governorate' => $this->governorate->{$language_column} ?? null,
            'city' => $this->city->{$language_column} ?? null,
            'countryCode' => $this->country_code ?? null,
            'governorateId' => $this->governorate_id ?? null,
            'cityId' => $this->city_id ?? null,
            'rate' => $this->rate ?? null,
            'socialAccounts' => [
                'contactNumber' => !$showNull ? ($this->contact_number ?? null) : null,
                'whatsappNumber' => !$showNull ? ($this->whatsapp_number ?? null) : null,
                'facebookUrl' => !$showNull ? ($this->facebook_url ?? null) : null,
                'twitterUrl' => !$showNull ? ($this->twitter_url ?? null) : null,
                'websiteUrl' => !$showNull ? ($this->website_url ?? null) : null,
            ],
            'isElite' => (bool)$this->is_elite,
            'isSelf' => $isSelf,
            'isPostsHidden' => $is_posts,
            'isFollowed' => $is_followed,
            'followStatus' => $follow_status,
            'isRated' => $isRated,
            'chatStatus' => !$showNull ? ($this->chats_privacy) : null,
            'profilePrivacy' => $this->profile_privacy,
            'followersCount' => $followers_count ?? null,
            'followedCount' => $followed_count ?? null,
            'profileVisible' => !$showNull,
            'lastLoginAt' => !$showNull ? ($this->last_login_at ? Carbon::make($this->last_login_at)->diffForHumans() : null) : null,
            'isOnline' => $this->is_online,
            'points' => $this->balance,
            'discount_percentage' => $this->discount_percentage,
            'addressLatitude' => !$showNull ? ($this->address_latitude ?? null) : null,
            'addressLongitude' => !$showNull ? ($this->address_longitude ?? null) : null,
            'allowed_posts_count'   =>  $this->allowed_posts_count,
            'allowed_offers_count'  =>  $this->allowed_offers_count,
        ];
    }
}
