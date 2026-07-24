<!-- Add Items Confirmation Modal -->
<x-form-modal wire:model="showAddModal" type="add" wire="store">
    <x-slot name="title">
        {{__('pages/categories/index.modal.add.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="subCategory_name_en">{{__('pages/categories/index.modal.add.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('subCategory.name_en') is-invalid @enderror"
                   id="subCategory_name_en"
                   name="name" wire:model.defer="subCategory.name_en">
            @error('subCategory.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="subCategory_name_ar">{{__('pages/categories/index.modal.add.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('subCategory.name_ar') is-invalid @enderror"
                   id="subCategory_name_ar"
                   name="name"
                   wire:model.defer="subCategory.name_ar">
            @error('subCategory.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        {{--<div class="form-group">
            <label for="subCategory_description">{{__('pages/categories/index.modal.add.inputs.description')}}</label>
            <textarea type="text" class="form-control @error('subCategory.description') is-invalid @enderror"
                      id="subCategory_description" wire:model.defer="subCategory.description"></textarea>
            @error('subCategory.description')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>--}}
        <div class="form-group"
             x-data="{ isUploading: false, progress: 0, isUploaded: false }"
             x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
             x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
             x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label
                for="category_image">{{__('pages/categories/index.modal.add.inputs.placeholders.choose_file')}}</label>
            <input type="file" wire:model.defer="subCategory.category_image" class="form-control h-auto"
                   id="category_image">
            <!-- Progress Bar -->
            <div x-show="isUploading">
                <progress max="100" x-bind:value="progress"></progress>
            </div>
            @error('subCategory.category_image') <span class="error">{{ $message }}</span> @enderror
            @isset($subCategory['category_image'])
                <img alt="{{$subCategory['category_image']}}" class="img-fluid mt-2" width="240"
                     src="{{ $subCategory['category_image']->temporaryUrl() }}">
            @endisset
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeAddModal" wire:loading.attr="disabled">
            {{__('pages/categories/index.modal.add.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/categories/index.modal.add.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Add Items Confirmation Modal -->

