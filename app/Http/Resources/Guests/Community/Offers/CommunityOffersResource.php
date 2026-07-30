<?php

namespace App\Http\Resources\Guests\Community\Offers;

use Carbon\Carbon;
use App\Helpers\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Media\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Shared\UsersLikes\UsersLikesResource;
use App\Http\Resources\Guests\Community\Comments\CommunityCommentsResource;

class CommunityOffersResource extends JsonResource
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

        //set the follow status
        $followed_status = __('api/guests/community/offers/offers.user-unfollowed');

        //check whether user liked this post
        $isLiked = false;

        $is_expired = ($this->expires_at && Carbon::make($this->expires_at)->isPast());

        if ($this->expires_at) {
            $expiresInDays = Carbon::make($this->expires_at)->diffInDays(now(), false);
            $expires_in = $expiresInDays >= 0 ? 0 : abs($expiresInDays) + 1;
        } else {
            $expires_in = $this->expires_in ?? null;
        }
        return [
            'id' => $this->id,
            'owner' => [
                'id' => $this->advertiser->id,
                'username' => $this->advertiser->username,
                'bio' => $this->advertiser->bio ?? null,
                'name' => $this->advertiser->name,
                'businessTypeName' => $this->advertiser->business_type ? $this->advertiser->business->{$name_column} : null,
                'imageUrl' => route('files.image.get', $this->advertiser->image) ?? null,
                'type' => $this->advertiser->user_type,
                'country' => optional($this->advertiser->country)->{$name_column},
                'governorate' => optional($this->advertiser->governorate)->{$name_column},
                'city' => optional($this->advertiser->city)->{$name_column},
                'governorateId' => $this->advertiser->governorate_id,
                'rate' => $rate,
                'isElite' => (bool)$this->advertiser->is_elite,
                'followedStatus' => $followed_status,
                'isSelf' => false,
                'chatStatus' => $this->advertiser->chats_privacy,
                'profilePrivacy' => $this->advertiser->profile_privacy,
                'isOnline' => $this->advertiser->is_online,
            ],
            'websiteUrl' => route('offer.index', ['id' => $this->id]),
            'content' => $this->content ?? null,
            'categoryId' => $this->category_id ?? null,
            'salePercentage' => $this->sale_percentage,
            'advertisementUrl' => $this->advertisement_url ?? null,
            'media' => MediaResource::collection($this->getMedia('offers')),
            'rate' => $this->rate ?? null,
            'expiresIn' => $expires_in,
            'expiresAt' => $this->expires_at ? Carbon::make($this->expires_at)->format('Y-m-d h:i A') : null,
            'isExpired' => $is_expired,
            'isRated' => false,
            'status' => $this->status,
            'statistics' => [
                'views' => $this->views_count ?? 0,
                'likes' => $this->likes_count ?? 0,
                'comments' => $this->comments_count ?? 0,
            ],
            'likesUsers'    => UsersLikesResource::collection($this->likes_users),
            'comments'    => CommunityCommentsResource::collection($this->users_comments),
            'permissions' => [
                'isAllowActions' => true,
                'isAllowLike' => false,
                'isAllowComment' => false,
                'isAllowRate' => false,
                'isAllowShare' => true,
                'isAllowReport' => true,
                'isAllowEdit' => false,
                'isAllowDelete' => false,
            ],
            'isLiked' => $isLiked,
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
