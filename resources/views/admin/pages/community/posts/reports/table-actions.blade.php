<div class="d-flex justify-content-around align-items-center">
    <div class="mx-1">
        <button @cannot('posts.reported') disabled @endcannot
                wire:click="$emitUp('setPostId', {{ $reported_id }})"
                class="btn btn-secondary"
                title="{{ __('datatable.actions') }}">
            <i class="icon-folder-open"></i>
        </button>
    </div>
    <div class="mx-1">
        <button @cannot('posts.reported') disabled @endcannot
                wire:click="showDeleteModal({{ $reported_id }})"
                class="btn btn-danger"
                title="{{ __('datatable.delete') }}">
            <i class="icon-trash"></i>
        </button>
    </div>
</div>
