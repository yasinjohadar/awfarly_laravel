<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('requests.contact.us') disabled @endcannot  wire:click="$emitUp('setContactId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    </div>
    @if($status === 'unread')
        <button title="Edit" @cannot('requests.contact.us') disabled
                @endcannot wire:click="showConfirmModal({{ $id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-eye"></i>
        </button>
    @else
        <button title="Edit" @cannot('requests.contact.us') disabled
                @endcannot wire:click="showConfirmModal({{ $id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-eye-blocked"></i>
        </button>
    @endif
</div>
