<?php

namespace App\Http\Livewire\Community\Chats;

use App\Http\Resources\Admins\Chats\ChatMessagesResource;
use App\Models\Chats\ChatChannel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CommunityChatsShowComponent extends Component
{
    public int $chat_id;
    public array $messages;
    public int $limit = 10;

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get chat
        $chat = ChatChannel::where('id', $this->chat_id)
            ->first();

        $messages = $chat->messages()
            ->latest()
            ->limit($this->limit)
            ->get()
            ->reverse();

        $users = $chat->users;

        $chat_users = collect([
            'right_user' => [
                'id' => $users->first()->user->id,
                'name' => $users->first()->user->name,
                'type' => $users->first()->user->user_type,
                'url' => ($users->first()->user->user_type === 'advertiser') ? route('admin.advertisers.show', $users->first()->user->id) : route('admin.customers.show', $users->first()->user->id),
            ],

            'left_user' => [
                'id' => $users->last()->user->id,
                'name' => $users->last()->user->name,
                'type' => $users->last()->user->user_type,
                'url' => ($users->last()->user->user_type === 'advertiser') ? route('admin.advertisers.show', $users->last()->user->id) : route('admin.customers.show', $users->last()->user->id),
            ],
        ]);
        $this->messages = ChatMessagesResource::collection($messages)->resolve();

        return view('admin.pages.community.chats.inquiry', [
            'chat' => $chat,
            'users' => $chat_users,
        ]);
    }

    public function loadMore()
    {
        //get chat
        $chat = ChatChannel::where('id', $this->chat_id)
            ->first();

        $messages_count = $chat->messages()
            ->count();

        if ($messages_count > $this->limit) {
            $this->limit += 5;
        }
    }
}
