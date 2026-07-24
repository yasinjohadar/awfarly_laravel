<div class="d-flex justify-content-around">
    <button @cannot('pages.inquiry') disabled @endcannot  wire:click="$emitUp('setPageId', {{ $id }})"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
</div>
