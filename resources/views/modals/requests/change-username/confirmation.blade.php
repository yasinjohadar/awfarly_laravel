<!-- Restore Items Confirmation Modal -->
<x-confirmation-modal wire:model="showConfirmModal" type="{{$confirm_type === 'declined' ? 'delete' : 'restore'}}">
    <x-slot name="title">
        {{__("pages/requests/username-change/index.modal.{$confirm_type}.title")}}
    </x-slot>

    <x-slot name="content">
        {{__("pages/requests/username-change/index.modal.{$confirm_type}.content")}}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showConfirmModal')" wire:loading.attr="disabled">
            {{__("pages/requests/username-change/index.modal.{$confirm_type}.cancel")}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" wire:click="updateRequest">
            {{__("pages/requests/username-change/index.modal.{$confirm_type}.submit")}}
        </x-primary-button>
    </x-slot>
</x-confirmation-modal>
<!-- /Restore Items Confirmation Modal -->

