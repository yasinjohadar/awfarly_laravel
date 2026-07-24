<div class="d-flex justify-content-around">
    <button title="Edit" @cannot('proposals.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary mx-1">
        <i class="icon-pencil7"></i>
    </button>
    @if($deleted_at)
        <button title="Restore" @cannot('proposals.delete') disabled
                @endcannot  wire:click="showRestoreModal({{ $id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-history"></i>
        </button>
    @endif
    <button @cannot('proposals.inquiry') disabled @endcannot  wire:click="$emitUp('setProposalId', {{ $id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
