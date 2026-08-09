<div class="d-flex justify-content-around">
    <button @cannot('cities.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})" class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    <button title="{{__('datatable.delete')}}" @cannot('cities.delete') disabled @endcannot
            wire:click="showDeleteModal({{ $id }})" class="btn btn-danger mx-1">
        <i class="icon-trash"></i>
    </button>
</div>
