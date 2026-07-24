<x-confirmation-modal wire:model="showSolveModal" type="restore">
    <x-slot name="title">
        {{ $solveModalTexts['title'] }}
    </x-slot>

    <x-slot name="content">
        {{ $solveModalTexts['content'] }}
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showSolveModal')" wire:loading.attr="disabled">
            {{ $solveModalTexts['cancel'] }}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" wire:click="solve()">
            {{ $solveModalTexts['submit'] }}
        </x-primary-button>
    </x-slot>
</x-confirmation-modal>
