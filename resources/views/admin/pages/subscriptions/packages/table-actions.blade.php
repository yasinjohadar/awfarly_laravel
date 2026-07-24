<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('packages.inquiry') disabled @endcannot  wire:click="$emitUp('setPackageId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-eye"></i>
        </button>
    </div>
    <div class="mx-2">
        <button title="Edit" @cannot('packages.edit') disabled
                @endcannot  wire:click="showEditModal({{ $id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
</div>
