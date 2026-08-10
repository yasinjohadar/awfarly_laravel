<x-dialog-modal wire:model="showCategoriesModal">
    <x-slot name="title">
        {{ __('pages/advertisers/index.modal.categories.title', ['name' => $viewed_user_name]) }}
    </x-slot>
    <x-slot name="content">
        @if($viewed_categories->isNotEmpty())
            <div class="d-flex flex-wrap" style="gap: .4rem">
                @foreach($viewed_categories as $category)
                    <span class="badge badge-primary p-2">
                        {{ App::getLocale() === 'ar' ? $category->name_ar : $category->name_en }}
                    </span>
                @endforeach
            </div>
        @else
            <div class="text-muted">
                {{ __('pages/advertisers/index.modal.categories.empty') }}
            </div>
        @endif
    </x-slot>
    <x-slot name="footer">
        <x-secondary-button wire:click="closeCategoriesModal" wire:loading.attr="disabled">
            {{ __('pages/advertisers/index.modal.categories.close') }}
        </x-secondary-button>
    </x-slot>
</x-dialog-modal>
