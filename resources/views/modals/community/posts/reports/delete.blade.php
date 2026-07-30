<x-confirmation-modal wire:model="showDeleteModal" type="delete">
    <x-slot name="title">
        {{ $deleteModalTexts['title'] }}
    </x-slot>

    <x-slot name="content">
        {{ $deleteModalTexts['content'] }}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showDeleteModal')" wire:loading.attr="disabled">
            {{ $deleteModalTexts['cancel'] }}
        </x-secondary-button>

        <x-danger-button wire:loading.attr="disabled" wire:click="delete()">
            {{ $deleteModalTexts['submit'] }}
        </x-danger-button>
    </x-slot>
</x-confirmation-modal>
