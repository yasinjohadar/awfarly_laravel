<?php

namespace App\Http\Resources\Admins\Chats;

use App\Http\Resources\Media\MediaResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $media = $this->getMedia('messages')->first();

          return [
            'id' => $this->id,
            'owner' => ChatsUsersResource::make($this->sender),
            'channel' => [
                'id' => $this->chat->id,
                'token' => $this->chat->token,
            ],
            'message' => $this->message,
            'media' => MediaResource::make($media),
            'createdAt' => Carbon::make($this->created_at)->format('Y-m-d h:i A'),
        ];
    }
}
