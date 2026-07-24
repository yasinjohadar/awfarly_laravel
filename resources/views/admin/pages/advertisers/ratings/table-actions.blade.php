
<div class="mx-1 w-auto d-inline">
    <button @cannot('ratings.inquiry') disabled @endcannot  wire:click="$emitUp('setRatingId', {{ $id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>

</div>
<div class="mx-1 w-auto d-inline">
    <button @cannot('ratings.inquiry') disabled @endcannot wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
</div>
