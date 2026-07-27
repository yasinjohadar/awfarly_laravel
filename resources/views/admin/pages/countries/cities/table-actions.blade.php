<div class="d-flex justify-content-around">
    <button @cannot('cities.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})" class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
</div>
