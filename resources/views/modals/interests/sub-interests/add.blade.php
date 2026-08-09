<!-- Add Items Confirmation Modal -->
<x-form-modal wire:model="showAddModal" type="add" wire="store">
    <x-slot name="title">
        {{__('pages/interests/index.modal.add.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="subInterest_name_en">{{__('pages/interests/index.modal.add.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('subInterest.name_en') is-invalid @enderror"
                   id="subInterest_name_en"
                   name="name" wire:model.defer="subInterest.name_en">
            @error('subInterest.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="subInterest_name_ar">{{__('pages/interests/index.modal.add.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('subInterest.name_ar') is-invalid @enderror"
                   id="subInterest_name_ar"
                   name="name"
                   wire:model.defer="subInterest.name_ar">
            @error('subInterest.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group"
             x-data="{ isUploading: false, progress: 0, isUploaded: false }"
             x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
             x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
             x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label
                for="interest_image">{{__('pages/interests/index.modal.add.inputs.placeholders.choose_file')}}</label>
            <input type="file" wire:model.defer="subInterest.interest_image" class="form-control h-auto"
                   id="interest_image">
            <!-- Progress Bar -->
            <div x-show="isUploading">
                <progress max="100" x-bind:value="progress"></progress>
            </div>
            @error('subInterest.interest_image') <span class="error">{{ $message }}</span> @enderror
            @isset($subInterest['interest_image'])
                <img alt="{{$subInterest['interest_image']}}" class="img-fluid mt-2" width="240"
                     src="{{ $subInterest['interest_image']->temporaryUrl() }}">
            @endisset
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeAddModal" wire:loading.attr="disabled">
            {{__('pages/interests/index.modal.add.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/interests/index.modal.add.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Add Items Confirmation Modal -->
