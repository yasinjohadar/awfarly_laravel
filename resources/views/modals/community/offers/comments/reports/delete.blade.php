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

        <x-primary-button wire:loading.attr="disabled" wire:click="delete()">
            {{ $deleteModalTexts['submit'] }}
        </x-primary-button>
    </x-slot>
</x-confirmation-modal>
