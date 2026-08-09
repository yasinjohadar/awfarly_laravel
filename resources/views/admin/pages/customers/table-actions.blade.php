<div class="d-flex justify-content-around">
    <button @cannot('customers.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})" class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    <button title="{{ __('pages/customers/index.actions.view_interests') }}" wire:click="showInterestsModal({{ $id }})" class="btn btn-secondary mx-1">
        <i class="icon-heart5"></i>
    </button>
</div>
