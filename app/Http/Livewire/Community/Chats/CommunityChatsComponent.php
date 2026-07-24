<?php

namespace App\Http\Livewire\Community\Chats;

use Livewire\Component;

class CommunityChatsComponent extends Component
{
    public ?int $chat_id = null;

    protected $listeners = [
        'setChatId'
    ];

    public function render()
    {
        return view('livewire.pages.community.chats.index');
    }

    /**
     * @param $id
     */
    public function setChatId($id = null)
    {
        $this->chat_id = $id;
    }
}
