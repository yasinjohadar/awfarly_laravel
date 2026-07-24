<?php

namespace App\Http\Resources\Advertisers\Community\Comments;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class RepliesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //get language column to show
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        //get likes count
        $likes = $this->likes()
            ->count() ?? 0;

        //check if current user is the owner of the post
        $isSelf = (optional($this->user)->id == Auth::guard('advertiser-api')->id() && optional($this->user)->user_type === 'advertiser');

        //check whether comment is liked or not
        $isAuth = Auth::guard('advertiser-api')->check();
        $isLiked = $isAuth ? Auth::guard('advertiser-api')->user()
            ->commentsLikes()
            ->where('comment_id', $this->id)
            ->exists() : false;

        return [
            'id' => $this->id,
            'content' => $this->comment,
            'statistics' => [
                'likes' => $this->likes_count,
            ],
            'replayable'    =>  (bool) !$this->parent,
            'owner' => [
                'id' => optional($this->user)->id,
                'name' => optional($this->user)->name,
                'imageUrl' => route('files.image.get', optional($this->user)->image) ?? null,
                'businessTypeName' => optional($this->user)->business_type ? optional($this->user)->business->{$name_column} : null,
                'type' => optional($this->user)->user_type,
                'isElite' => (bool)optional($this->user)->is_elite,
                'isSelf' => $isSelf,
                'chatStatus' => optional($this->user)->chats_privacy,
                'profilePrivacy' => optional($this->user)->profile_privacy,
                'isOnline' => optional($this->user)->is_online,
            ],
            'isLiked' => $isLiked,
            'permissions' => [
                'isAllowActions' => $isAuth,
                'isAllowLike' => $isAuth,
                'isAllowReport' => !$isSelf,
                'isAllowEdit' => $isSelf,
                'isAllowDelete' => $isSelf,
            ],
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
