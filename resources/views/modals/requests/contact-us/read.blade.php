<!-- Restore Items Confirmation Modal -->
<x-confirmation-modal wire:model="showConfirmModal" type="restore">
    <x-slot name="title">
        {{$ToggleReadTexts['title'] ?? null}}
    </x-slot>

    <x-slot name="content">
        {{$ToggleReadTexts['content'] ?? null}}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showConfirmModal')" wire:loading.attr="disabled">
            {{__('pages/requests/contact-us/inquiry.modal.confirm.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" wire:click="markAsRead">
            {{__('pages/requests/contact-us/inquiry.modal.confirm.submit')}}
        </x-primary-button>
    </x-slot>
</x-confirmation-modal>
<!-- /Restore Items Confirmation Modal -->

