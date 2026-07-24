<?php

namespace App\Http\Resources\Advertisers\Community\Comments;

use App\Helpers\Files;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommunityCommentsResource extends JsonResource
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
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        //get likes count
        $likes = $this->likes()
            ->count() ?? 0;
        //check if current user is the owner of the post
        $isSelf = ($this->user_id == Auth::guard('advertiser-api')->id() && optional($this->user)->user_type === 'advertiser');

        //check whether comment is liked or not
        $isLiked = Auth::guard('advertiser-api')->user()
            ->commentsLikes()
            ->where('comment_id', $this->id)
            ->exists();

        return [
            'id' => $this->id,
            'content' => $this->comment,
            'statistics' => [
                'likes' => $this->likes_count,
            ],
            'replies'   =>  RepliesResource::collection($this->replays),
            'replayable'    =>  true,
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
