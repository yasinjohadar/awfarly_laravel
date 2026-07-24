<x-confirmation-modal wire:model="showDeleteModal" type="delete">
    <x-slot name="title">
        {{{__('pages/subscriptions/payments/show.modal.delete.title')}}}
    </x-slot>

    <x-slot name="content">
        {{{__('pages/subscriptions/payments/show.modal.delete.content')}}}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showDeleteModal')" wire:loading.attr="disabled">
            {{{__('pages/subscriptions/payments/show.modal.delete.cancel')}}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" wire:click="deleteImage({{$image_id}})">
            {{{__('pages/subscriptions/payments/show.modal.delete.submit')}}}
        </x-primary-button>
    </x-slot>
</x-confirmation-modal>
