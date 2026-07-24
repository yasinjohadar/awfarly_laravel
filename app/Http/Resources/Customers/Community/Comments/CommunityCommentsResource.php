<?php

namespace App\Http\Resources\Customers\Community\Comments;

use Carbon\Carbon;
use App\Helpers\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Advertisers\Community\Comments\RepliesResource;

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

        //check if current user is the owner of the post
        $isSelf = ($this->user?->id == Auth::guard('customer-api')->id() && $this->user?->user_type === 'customer');

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
            'replies'   =>  RepliesResource::collection($this->replays),
            'replayable'    =>  true,
            'owner' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'imageUrl' => route('files.image.get', $this->user?->image) ?? null,
                'businessTypeName' => $this->user?->business_type ? $this->user?->business->{$name_column} : null,
                'type' => $this->user?->user_type,
                'isElite' => (bool)$this->user?->is_elite,
                'isSelf' => $isSelf,
                'chatStatus' => $this->user?->chats_privacy,
                'profilePrivacy' => $this->user?->profile_privacy,
                'isOnline' => $this->user?->is_online,
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
