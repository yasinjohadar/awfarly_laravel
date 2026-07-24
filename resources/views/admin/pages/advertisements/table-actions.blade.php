<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('advertisements.inquiry') disabled @endcannot  wire:click="$emitUp('setAdvertisementId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-eye"></i>
        </button>
        <a @cannot('advertisements.edit') disabled @endcannot href="{{route('admin.advertisements.edit', $id)}}"
                class="btn btn-secondary">
            <i class="icon-pencil7"></i>
        </a>
    </div>
</div>
