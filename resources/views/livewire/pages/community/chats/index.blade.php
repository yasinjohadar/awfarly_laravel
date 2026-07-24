<div class="card">
    <div class="card-header">
        @if($chat_id)
            <h5 class="card-title">{!! __('pages/community/chats/show.content.title', ['id' => $chat_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/chats/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($chat_id)
            @livewire('community.chats.community-chats-show-component', ['chat_id' => $chat_id], key($chat_id))
        @else
            <div class="form-group">
                @livewire('community.chats.community-chats-inquiry-component')
            </div>
        @endif
    </div>
</div>
