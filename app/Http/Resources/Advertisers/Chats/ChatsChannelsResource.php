<?php

namespace App\Http\Resources\Advertisers\Chats;

use App\Models\Users\Advertisers\AdvertiserUser;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatsChannelsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get unread messages count
        $unread_messages_count = $this->messages()
            ->where('is_read', false)
            ->whereHasMorph('sender', '*', function ($q, $type) {
                if ($type === AdvertiserUser::class) {
                    return $q->where('id', '!=', Auth::guard('advertiser-api')->id());
                }
                return $q;
            })
            ->count();

        //get user
        $user = $this->users()
            ->whereHasMorph('user', '*', function ($q, $type) {
                if ($type === AdvertiserUser::class) {
                    $q->where('id', '!=', Auth::guard('advertiser-api')->id());
                }
            })
            ->where('chat_id', $this->id)
            ->first();


        return [
            'id' => $this->id,
            'token' => $this->token,
            'toUser' => ChatsUsersResource::make($user->user) ?? null,
            'lastMessage' => ChatMessagesResource::make($this->messages()->latest()->first()) ?? null,
            'unreadMessagesCount' => $unread_messages_count ?? 0,
            'lastMessageAt' => $this->last_message_at ? Carbon::make($this->last_message_at)->diffForHumans() : null,
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
