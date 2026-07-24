<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('requests.username.change') disabled @endcannot  wire:click="$emitUp('setRequestId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    </div>
</div>
