<x-dialog-modal wire:model="showInterestsModal">
    <x-slot name="title">
        {{ __('pages/customers/index.modal.interests.title', ['name' => $viewed_user_name]) }}
    </x-slot>
    <x-slot name="content">
        @if($viewed_interests->isNotEmpty())
            <div class="d-flex flex-wrap" style="gap: .4rem">
                @foreach($viewed_interests as $interest)
                    <span class="badge badge-primary p-2">
                        {{ App::getLocale() === 'ar' ? $interest->name_ar : $interest->name_en }}
                    </span>
                @endforeach
            </div>
        @else
            <div class="text-muted">
                {{ __('pages/customers/index.modal.interests.empty') }}
            </div>
        @endif
    </x-slot>
    <x-slot name="footer">
        <x-secondary-button wire:click="closeInterestsModal" wire:loading.attr="disabled">
            {{ __('pages/customers/index.modal.interests.close') }}
        </x-secondary-button>
    </x-slot>
</x-dialog-modal>
