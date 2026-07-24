<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$country['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="code">{{__('pages/countries/index.modal.edit.inputs.code')}}</label>
            <input type="text" class="form-control @error('country.code') is-invalid @enderror" id="code"
                   name="code"
                   wire:model.defer="country.code"
                   >
            @error('country.code')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_en">{{__('pages/countries/index.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('country.name_en') is-invalid @enderror" id="name_en"
                   name="name"
                   wire:model.defer="country.name_en"
                   >
            @error('country.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_ar">{{__('pages/countries/index.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('country.name_ar') is-invalid @enderror" id="name_ar"
                   name="name"
                   wire:model.defer="country.name_ar"
                   >
            @error('country.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        {{--<div class="form-group">
            <label for="mobile_code">{{__('pages/countries/index.modal.edit.inputs.mobile_code')}}</label>
            <input type="text" class="form-control @error('country.mobile_code') is-invalid @enderror" id="mobile_code"
                   name="mobile_code"
                   wire:model.defer="country.mobile_code"
                   >
            @error('country.mobile_code')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>--}}
        <div class="form-group">
            <label for="is_active">{{__('pages/countries/index.modal.edit.inputs.is_active')}}</label>
            <select class="form-control" wire:model.defer="country.is_active" id="is_active">
                <option value="1">
                    {{__('pages/countries/index.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/countries/index.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('country.is_active')
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
