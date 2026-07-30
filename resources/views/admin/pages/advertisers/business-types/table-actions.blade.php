<div class="d-flex justify-content-around align-items-center">
    <button @cannot('business.types.edit') disabled @endcannot
            wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary mx-1"
            title="{{ __('datatable.edit') }}">
        <i class="icon-pencil7"></i>
    </button>
    <button @cannot('business.types.delete') disabled @endcannot
            wire:click="showDeleteModal({{ $id }})"
            class="btn btn-danger mx-1"
            title="{{ __('datatable.delete') }}">
        <i class="icon-trash"></i>
    </button>
</div>
