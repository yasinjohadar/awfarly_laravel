<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$category['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="name_en">{{__('pages/categories/index.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('category.name_en') is-invalid @enderror" id="name_en"
                   name="name"
                   wire:model.defer="category.name_en"
            >
            @error('category.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_ar">{{__('pages/categories/index.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('category.name_ar') is-invalid @enderror" id="name_ar"
                   name="name"
                   wire:model.defer="category.name_ar"
            >
            @error('category.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        {{--<div class="form-group">
            <label for="description">{{__('pages/categories/index.modal.edit.inputs.description')}}</label>
            <textarea type="text" class="form-control @error('category.description') is-invalid @enderror"
                      id="description"
                      wire:model.defer="category.description"
                      ></textarea>
            @error('category.description')
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
            <label for="new_image">{{__('pages/categories/index.modal.edit.inputs.image')}}</label>
            <input type="file" wire:model.defer="category.new_image" class="form-control h-auto" id="new_image">
            <!-- Progress Bar -->
            <div x-show="isUploading">
                <progress max="100" x-bind:value="progress"></progress>
            </div>
            @error('category.new_image') <span class="error">{{ $message }}</span> @enderror

            @isset($category['new_image'])
                <img alt="{{$category['new_image']}}" class="img-fluid mt-2" width="240"
                     src="{{ $category['new_image']->temporaryUrl() }}">
            @endisset
        </div>
        <div class="form-group">
            <label for="is_active">{{__('pages/categories/index.modal.edit.inputs.is_active')}}</label>
            <select class="form-control" wire:model.defer="category.is_active" id="is_active">
                <option value="1">
                    {{__('pages/categories/index.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/categories/index.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('category.is_active')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{ $editModalTexts['cancel'] }}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{ $editModalTexts['submit'] }}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->


@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#new_image').val(null);
        });
    </script>
@endpush
