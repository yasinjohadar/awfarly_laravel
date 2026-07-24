<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('chats.inquiry') disabled @endcannot  wire:click="$emitUp('setChatId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    </div>
</div>
