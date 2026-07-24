@if(count($selected) > 0)
    <div class="mx-3">
        <div class="alert alert-indigo d-flex align-items-center">
            {{__('datatable.selected', ['count'=>count($selected)], Auth::guard('admin')->user()->language)}}
            <div class="ml-auto">
                <button class="btn btn-outline-indigo" wire:click="showDeleteModal">
                    {{__('datatable.delete')}}
                </button>
            </div>
        </div>
    </div>
@endif
@if($has_delete)
    <!-- Delete Items Confirmation Modal -->
    <x-confirmation-modal wire:model="showDeleteModal" type="delete">
        <x-slot name="title">
            {{ $deleteModalTexts['title'] ?? null }}
        </x-slot>

        <x-slot name="content">
            {{ $deleteModalTexts['content'] ?? null }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('showDeleteModal')" wire:loading.attr="disabled">
                {{ $deleteModalTexts['cancel'] ?? null }}
            </x-secondary-button>

            <x-danger-button wire:loading.attr="disabled" wire:click="deleteSelected">
                {{ $deleteModalTexts['submit'] ?? null }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
    <!-- /Delete Items Confirmation Modal -->
@endif
