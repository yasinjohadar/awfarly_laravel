<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('advertisements.inquiry') disabled @endcannot  wire:click="$emitUp('setAdvertisementId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-eye"></i>
        </button>
    </div>
</div>
