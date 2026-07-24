<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$advertisement['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/advertisements/show.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group" wire:ignore>
            <label for="media">{{__('pages/advertisements/show.content.files')}}</label>
            <input type="file" class="form-control h-100 @error('media') is-invalid @enderror"
                   id="media"
                   name="media"
                   wire:model.defer="media" multiple>
            @error('media')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{__('pages/advertisements/show.modal.edit.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/advertisements/show.modal.edit.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
