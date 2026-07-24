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

{{-- Delete Items Confirmation Modal  --}}
<x-form-modal wire:model="showDeleteModal" type="delete" wire="deleteSelected">
    <x-slot name="title">
        {{ $deleteModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <form wire:submit.prevent="deleteSelected">
            <div class="font-weight-bold">{{$deleteModalTexts['select-option']}}</div>
            <fieldset class="form-group">
                <div class="form-check form-check-inline">
                    <input value="soft" wire:model.defer="delete_type" name="delete_type"
                           class="form-check-input" type="radio" id="soft">
                    <label class="form-check-label"
                           for="soft">{{$deleteModalTexts['soft-delete']}}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input value="permanent" wire:model.defer="delete_type" name="delete_type"
                           class="form-check-input" type="radio" id="permanent">
                    <label class="form-check-label"
                           for="permanent">{{$deleteModalTexts['permanent-delete']}}</label>
                </div>
            </fieldset>
            {{ $deleteModalTexts['content'] }}
        </form>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeDeleteModal" wire:loading.attr="disabled">
            {{ $deleteModalTexts['cancel'] }}
        </x-secondary-button>

        <x-danger-button type="submit">
            {{ $deleteModalTexts['submit'] }}
        </x-danger-button>
    </x-slot>
</x-form-modal>
{{-- Delete Items Confirmation Modal --}}
