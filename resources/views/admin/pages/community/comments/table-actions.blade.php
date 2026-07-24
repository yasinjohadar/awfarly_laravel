<div class="d-flex justify-content-around">
    {{--<button @cannot('customers.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})" class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>--}}
    @if($deleted_at)
        <button title="Restore" @cannot('customers.edit') disabled @endcannot  wire:click="showRestoreModal({{ $id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-history"></i>
        </button>
    @endif
</div>
