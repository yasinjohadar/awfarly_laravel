<?php

namespace App\Http\Resources\Advertisers\Chats;

use App\Http\Resources\Media\MediaResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChatMessagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get current user
        $user = Auth::guard('advertiser-api')->user();

        $is_owner = ($this->sender->user_type === $user->user_type && $this->sender->id === $user->id);

        return [
            'id' => $this->id,
            'owner' => ChatsUsersResource::make($this->sender),
            'channel' => [
                'id' => $this->chat->id,
                'token' => $this->chat->token,
            ],
            'message' => $this->message,
            'media' => MediaResource::make($this->getMedia('messages')->first()),
            'isRead' => $this->is_read,
            'isOwner' => $is_owner,
            'createdAt' => Carbon::make($this->created_at)->diffForHumans(),
        ];
    }
}
