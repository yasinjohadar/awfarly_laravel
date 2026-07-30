<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('payments.inquiry') disabled @endcannot  wire:click="$emitUp('setPaymentId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-eye"></i>
        </button>
    </div>
    <div class="mx-2">
        <button title="Edit" @cannot('payments.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})"
                class="btn btn-secondary">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    @if($deleted_at)
        <div class="mx-2">
            <button title="Restore" @cannot('payments.edit') disabled @endcannot  wire:click="showRestoreModal({{ $id }})"
                    class="btn btn-secondary">
                <i class="icon-history"></i>
            </button>
        </div>
    @else
        <div class="mx-2">
            <button title="{{ __('datatable.delete') }}" @cannot('payments.delete') disabled @endcannot
                    wire:click="showDeleteModal({{ $id }})"
                    class="btn btn-danger">
                <i class="icon-trash"></i>
            </button>
        </div>
    @endif
</div>
