<x-confirmation-modal wire:model="showDeletePostModal" type="delete">
    <x-slot name="title">
        {{ $deletePostModalTexts['title'] ?? null }}
    </x-slot>

    <x-slot name="content">
        {{ $deletePostModalTexts['content'] ?? null }}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showDeletePostModal')" wire:loading.attr="disabled">
            {{ $deletePostModalTexts['cancel'] ?? null }}
        </x-secondary-button>

        <x-danger-button wire:loading.attr="disabled" wire:click="deletePost">
            {{ $deletePostModalTexts['submit'] ?? null }}
        </x-danger-button>
    </x-slot>
</x-confirmation-modal>
