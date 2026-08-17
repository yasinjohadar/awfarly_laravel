<div class="d-flex justify-content-around">
    @if(!$deleted_at && ($status ?? null) === 'pending')
        <button title="{{ __('pages/community/offers/inquiry.modal.edit.inputs.approved') }}"
                @cannot('offers.edit') disabled @endcannot
                wire:click="approve({{ $id }})"
                wire:loading.attr="disabled"
                wire:target="approve({{ $id }})"
                onclick="return confirm('{{ __('pages/community/offers/inquiry.modal.edit.inputs.approved') }}?')"
                class="btn btn-success mx-1">
            <i class="icon-checkmark3"></i>
        </button>
    @endif

    <button title="Edit" @cannot('offers.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})" class="btn btn-secondary mx-1">
        <i class="icon-pencil7"></i>
    </button>
    @if($deleted_at)
        <button title="Restore" @cannot('offers.edit') disabled @endcannot  wire:click="showRestoreModal({{ $id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-history"></i>
        </button>
    @endif

    <div class="mx-2">
        <button @cannot('offers.inquiry') disabled @endcannot  wire:click="$emitUp('setOfferId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    </div>
</div>
