<div class="mx-2">
    <button @cannot('posts.reported') disabled @endcannot  wire:click="$emitUp('setPostId', {{ $reported_id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
