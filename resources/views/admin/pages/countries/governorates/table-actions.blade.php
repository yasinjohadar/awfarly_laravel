<div class="d-flex justify-content-around">
    <button @cannot('governorates.edit') disabled @endcannot wire:click="showEditModal({{ $id }})" class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    @canany(['cities.inquiry', 'cities.add'])
        <button wire:click="$emitUp('setGovernorateId', {{ $id }})" class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    @endcanany
</div>
