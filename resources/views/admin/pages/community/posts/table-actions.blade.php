<div class="d-flex justify-content-around">
    <button title="Edit" @cannot('posts.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})" class="btn btn-secondary mx-1">
        <i class="icon-pencil7"></i>
    </button>

    @if($deleted_at)
        <div class="mx-2">
            <button title="Restore" @cannot('posts.edit') disabled @endcannot  wire:click="showRestoreModal({{ $id }})"
                    class="btn btn-secondary mx-1">
                <i class="icon-history"></i>
            </button>
        </div>
    @endif

    <div class="mx-2">
        <a class="btn btn-secondary" title="Comments" @cannot('posts.inquiry') disabled
           @endcannot href="{{route('admin.community.comments.show', $id)}}">
            <i class="icon-comments"></i>
        </a>
    </div>
    <div class="mx-2">
        <button @cannot('posts.inquiry') disabled @endcannot  wire:click="$emitUp('setPostId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    </div>
</div>
