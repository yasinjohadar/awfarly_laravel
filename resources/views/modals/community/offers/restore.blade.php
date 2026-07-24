@if($has_restore)
    <!-- Restore Items Confirmation Modal -->
    <x-confirmation-modal wire:model="showRestoreModal" type="restore">
        <x-slot name="title">
            {{ $restoreModalTexts['title'] }}
        </x-slot>

        <x-slot name="content">
            {{ $restoreModalTexts['content'] }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('showRestoreModal')" wire:loading.attr="disabled">
                {{ $restoreModalTexts['cancel'] }}
            </x-secondary-button>

            <x-primary-button wire:loading.attr="disabled" wire:click="restore({{$this->restore ?? null}})">
                {{ $restoreModalTexts['submit'] }}
            </x-primary-button>
        </x-slot>
    </x-confirmation-modal>
    <!-- /Restore Items Confirmation Modal -->
@endif
