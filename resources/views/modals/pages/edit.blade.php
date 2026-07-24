<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$page_data['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        {{--<div class="form-group">
            <label for="slug">{{__('pages/pages/index.modal.edit.inputs.slug')}}</label>
            <input type="text" class="form-control @error('page_data.slug') is-invalid @enderror" id="slug"
                   wire:model.defer="page_data.slug">
            @error('page_data.slug')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="title_en">{{__('pages/pages/index.modal.edit.inputs.title_en')}}</label>
            <input type="text" class="form-control @error('page_data.title_en') is-invalid @enderror" id="title_en"
                   wire:model.defer="page_data.title_en">
            @error('page_data.title_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="title_ar">{{__('pages/pages/index.modal.edit.inputs.title_ar')}}</label>
            <input type="text" class="form-control @error('page_data.title_ar') is-invalid @enderror" id="title_ar"
                   wire:model.defer="page_data.title_ar">
            @error('page_data.title_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>--}}
        <div class="form-group" wire:ignore x-data="{contents_en: @entangle('contents_en').defer,}"
             @reload-summernote.window="{
                $($refs.contents_en).summernote('code', contents_en);
             }"
             x-init="$nextTick(() => {
                $($refs.contents_en).summernote({
                    code: contents_en,
                    callbacks: {
                        onChange: function (contents, $editable) {
                            contents_en = contents;
                        }
                    }
                });
            })">
            <label for="contents_en">{{__('pages/pages/index.modal.edit.inputs.contents_en')}}</label>
            <textarea rows="10" type="text"
                      class="form-control summernote summernote-borderless @error('contents_en') is-invalid @enderror"
                      id="contents_en" x-model="contents_en" x-cloak x-bind:value="contents_en" x-ref="contents_en"
                      wire:model.defer="contents_en"></textarea>
            @error('contents_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group" wire:ignore x-data="{contents_ar: @entangle('contents_ar').defer,}"
             @reload-summernote.window="{
                $($refs.contents_ar).summernote('code', contents_ar);
             }"
             x-init="$nextTick(() => {
                $($refs.contents_ar).summernote({
                    code: contents_ar,
                    callbacks: {
                        onChange: function (contents, $editable) {
                            contents_ar = contents;
                        }
                    }
                });
            })">
            <label for="contents_ar">{{__('pages/pages/index.modal.edit.inputs.contents_ar')}}</label>
            <textarea rows="10" type="text"
                      x-model="contents_ar" x-bind:value="contents_ar" x-cloak id="contents_ar" x-ref="contents_ar"
                      class="form-control summernote summernote-borderless @error('contents_ar') is-invalid @enderror"
                      wire:model.defer="contents_ar"></textarea>
            @error('contents_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>{{--
        <div class="form-group">
            <label for="is_protected">{{__('pages/pages/index.modal.edit.inputs.is_protected')}}</label>
            <select class="form-control" id="is_protected" wire:model.defer="page_data.is_protected" required>
                <option value="1">{{__('pages/pages/index.modal.edit.inputs.boolean.yes')}}</option>
                <option value="0">{{__('pages/pages/index.modal.edit.inputs.boolean.no')}}</option>
            </select>
            @error('page_data.is_protected')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="is_active">{{__('pages/pages/index.modal.edit.inputs.is_active')}}</label>
            <select class="form-control" id="is_active" wire:model.defer="page_data.is_active" required>
                <option value="1">{{__('pages/pages/index.modal.edit.inputs.boolean.yes')}}</option>
                <option value="0">{{__('pages/pages/index.modal.edit.inputs.boolean.no')}}</option>
            </select>
            @error('page_data.is_active')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>--}}
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
