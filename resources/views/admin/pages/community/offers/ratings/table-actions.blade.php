<div class="mx-2">
    <button @cannot('ratings.inquiry') disabled @endcannot  wire:click="$emitUp('setRatingId', {{ $id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
