<?php

namespace App\Http\Resources\Customers\Community\Offers\Comments;

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
        $isSelf = ($this->user->id == Auth::guard('advertiser-api')->id() && $this->user->user_type === 'advertiser');

        //check whether comment is liked or not
        $isLiked = Auth::guard('customer-api')->user()
            ->commentsLikes()
            ->where('comment_id', $this->id)
            ->exists();

        return [
            'id' => $this->id,
            'content' => $this->comment,
            'statistics' => [
                'likes' => $this->likes_count,
            ],
            'replayable'    =>  (bool) !$this->parent,
            'owner' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'imageUrl' => route('files.image.get', $this->user->image) ?? null,
                'businessTypeName' => $this->user->business_type ? $this->user->business->{$name_column} : null,
                'type' => $this->user->user_type,
                'isElite' => (bool)$this->user->is_elite,
                'isSelf' => $isSelf,
                'chatStatus' => $this->user->chats_privacy,
                'profilePrivacy' => $this->user->profile_privacy,
                'isOnline' => $this->user->is_online,
            ],
            'isLiked' => $isLiked,
            'permissions' => [
                'isAllowActions' => true,
                'isAllowLike' => true,
                'isAllowReport' => !$isSelf,
                'isAllowEdit' => $isSelf,
                'isAllowDelete' => $isSelf,
            ],
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
