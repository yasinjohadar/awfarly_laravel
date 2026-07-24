<?php

namespace App\Http\Resources\Guests\Community\Posts;

use Carbon\Carbon;
use App\Helpers\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Media\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Shared\UsersLikes\UsersLikesResource;
use App\Http\Resources\Guests\Community\Comments\CommunityCommentsResource;

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
        $rate = $this->user?->rate ?? null;

        return [
            'id' => $this->id,
            'content' => $this->content,
            'websiteUrl' => route('post.index', ['id' => $this->id]),
            'media' => MediaResource::collection($this->getMedia('posts')),
            'statistics' => [
                'views' => $this->views_count,
                'likes' => $this->likes_count,
                'comments' => $this->comments_count,
            ],
            'likesUsers'    => UsersLikesResource::collection($this->likes_users),
            'comments'    => CommunityCommentsResource::collection($this->users_comments),
            'categoryId' => $this->category_id ?? null,
            'owner' => [
                'id' => $this->user?->id,
                'username' => $this->user?->username,
                'bio' => $this->user?->bio ?? null,
                'name' => $this->user?->name,
                'businessTypeName' => $this->user?->business_type ? $this->user?->business?->{$name_column} : null,
                'imageUrl' => route('files.image.get', $this->user?->image) ?? null,
                'type' => $this->user?->user_type,
                'country' => $this->user?->country->{$name_column},
                'city' => $this->user?->city->{$name_column},
                'rate' => $rate,
                'isFollowed' => false,
                'followStatus' => 'unfollowed',
                'isElite' => (bool)$this->user?->is_elite,
                'chatStatus' => $this->user?->chats_privacy,
                'profilePrivacy' => $this->user?->profile_privacy,
                'isSelf' => false,
                'isOnline' => $this->user?->is_online,
            ],
            'isLiked' => false,
            'permissions' => [
                'isAllowActions' => true,
                'isAllowLike' => false,
                'isAllowComment' => false,
                'isAllowShare' => true,
                'isAllowSave' => false,
                'isAllowGetNotifications' => false,
                'isAllowHideCompanyPosts' => false,
                'isAllowReport' => true,
                'isAllowEdit' => false,
                'isAllowDelete' => false,
            ],
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
