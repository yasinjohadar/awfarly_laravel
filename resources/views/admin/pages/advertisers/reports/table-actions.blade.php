<div class="mx-2">
    <button @cannot('advertisers.inquiry') disabled @endcannot  wire:click="$emitUp('setAdvertiserId', {{ $reported_id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
