<div class="mx-2">
    <button @cannot('comments.reported') disabled @endcannot  wire:click="$emitUp('setCommentId', {{ $reported_id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
