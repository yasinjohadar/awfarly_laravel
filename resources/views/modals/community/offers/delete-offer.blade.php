<x-confirmation-modal wire:model="showOfferDeleteModal" type="delete">
    <x-slot name="title">
        {{ __('pages/community/offers/show.modal.delete_offer.title') }}
    </x-slot>

    <x-slot name="content">
        {{ __('pages/community/offers/show.modal.delete_offer.content') }}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeOfferDeleteModal" wire:loading.attr="disabled">
            {{ __('pages/community/offers/show.modal.delete_offer.cancel') }}
        </x-secondary-button>

        <x-danger-button wire:loading.attr="disabled" wire:click="deleteOffer">
            {{ __('pages/community/offers/show.modal.delete_offer.submit') }}
        </x-danger-button>
    </x-slot>
</x-confirmation-modal>
