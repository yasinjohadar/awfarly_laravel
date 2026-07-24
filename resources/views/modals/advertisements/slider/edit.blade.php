<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$advertisement['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/advertisements/show.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="advertisement_url">{{__('pages/advertisements/side/create.content.inputs.url')}}</label>
            <input type="url" class="form-control @error('advertisement_url') is-invalid @enderror"
                   id="advertisement_url"
                   name="advertisement_url" wire:model.defer="advertisement_url">
            @error('advertisement_url')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group"
             x-data="{ isUploading: false, progress: 0, isUploaded: false }"
             x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
             x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
             x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label for="image">{{__('pages/advertisements/side/show.content.image')}}</label>
            <input type="file" wire:model.defer="image" class="form-control h-auto" id="image">
            <!-- Progress Bar -->
            <div x-show="isUploading">
                <progress max="100" x-bind:value="progress"></progress>
            </div>
            @if($image)
                <div x-show="isUploaded">
                    <img alt="{{$image}}" class="img-fluid mt-2" width="240"
                         src="{{ $image->temporaryUrl() }}">
                </div>
            @endif
            @error('image') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="starts_at">{{__('pages/advertisements/side/create.content.inputs.starts_at')}}</label>
            <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at"
                   wire:model.defer="starts_at"/>
            @error('starts_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group">
            <label for="ends_at">{{__('pages/advertisements/side/create.content.inputs.ends_at')}}</label>
            <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at"
                   wire:model.defer="ends_at"/>
            @error('ends_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
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
