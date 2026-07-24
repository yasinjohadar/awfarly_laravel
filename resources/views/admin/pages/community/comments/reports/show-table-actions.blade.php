<div class="d-flex justify-content-around">
    <button @cannot('posts.reported') disabled @endcannot wire:click="showMoreModal({{ $id }})" class="btn btn-secondary">
        <i class="icon-file-text2"></i>
    </button>
</div>

