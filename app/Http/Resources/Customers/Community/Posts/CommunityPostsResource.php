<?php

namespace App\Http\Resources\Customers\Community\Posts;

use Carbon\Carbon;
use App\Helpers\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Media\MediaResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Shared\UsersLikes\UsersLikesResource;
use App\Http\Resources\Customers\Community\Comments\CommunityCommentsResource;

class CommunityPostsResource extends JsonResource
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
        $isSelf = ($this->user->id == Auth::guard('customer-api')->id() && $this->user->user_type === 'customer');

        //check whether this user is being followed or by the current logged user or not
        $followed = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', $this->user->class)
            ->where('followed_id', $this->user->id)
            ->first();

        if ($followed) {
            $follow_status = $followed->status;
        } else {
            $follow_status = 'unfollowed';
        }


        //check whether user liked this post
        $isLiked = Auth::guard('customer-api')->user()
            ->postsLikes()
            ->where('post_id', $this->id)
            ->exists();

        //check whether user is subscribed to this post
        $is_subscribed = Auth::guard('customer-api')->user()
            ->subscribedPosts()
            ->where('post_id', $this->id)
            ->exists();

        //check whether user saved this post or not
        $is_saved = Auth::guard('customer-api')->user()
            ->savedPosts()
            ->where('post_id', $this->id)
            ->exists();


        $false_permissions = !($this->user->status === 'inactive' || Auth::guard('customer-api')->user()->status === 'inactive');

        //check if this advertiser is Hidden or not
        $isHidden = Auth::guard('customer-api')->user()
            ->hiddenAdvertisers()
            ->where('advertiser_id', $this->user->id)
            ->exists();

        return [
            'id' => $this->id,
            'content' => $this->content,
            'websiteUrl' => route('post.index', ['id' => $this->id]),
            'media' => MediaResource::collection($this->getMedia('posts')),
            'statistics' => [
                'views' => $this->views_count,
                'likes' => $this->likes_count,
                'comments' => $this->users_comments()->count() ?? 0,
            ],
            'likesUsers'    => UsersLikesResource::collection($this->likes_users),
            'comments'    => $this->users_comments->count() > 0 ? CommunityCommentsResource::collection($this->users_comments) : [],

            'categoryId' => $this->category_id ?? null,
            'owner' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'bio' => $this->user->bio ?? null,
                'name' => $this->user->name,
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
            'isHidden' => $isHidden,
            'isLiked' => $isLiked,
            'isSubscribed' => $is_subscribed,
            'isSaved' => $is_saved,
            'permissions' => [
                'isAllowActions' => $false_permissions,
                'isAllowLike' => $false_permissions,
                'isAllowComment' => $false_permissions,
                'isAllowShare' => true,
                'isAllowSave' => $false_permissions,
                'isAllowGetNotifications' => $false_permissions,
                'isAllowHideCompanyPosts' => !$isSelf,
                'isAllowReport' => !$isSelf,
                'isAllowEdit' => $isSelf,
                'isAllowDelete' => $isSelf,
            ],
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
