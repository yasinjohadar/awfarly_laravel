<div class="d-flex justify-content-around">
    <button @cannot('countries.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    <button @cannot('countries.inquiry') disabled @endcannot  wire:click="$emitUp('setCountryId', {{ $id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
