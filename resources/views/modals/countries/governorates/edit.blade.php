<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$governorate['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="governorate_country_code">{{__('pages/countries/governorates/index.content.datatable.country')}}</label>
            <select class="form-control @error('governorate.country_code') is-invalid @enderror"
                    id="governorate_country_code"
                    wire:model.defer="governorate.country_code">
                <option value="">{{__('pages/countries/governorates/create.content.inputs.country_code')}}</option>
                @foreach(\App\Models\Countries\Country::orderBy('order')->get() as $country)
                    <option value="{{ $country->code }}">
                        {{ app()->getLocale() === 'ar' ? $country->name_ar : $country->name_en }}
                    </option>
                @endforeach
            </select>
            @error('governorate.country_code')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_en">{{__('pages/countries/governorates/index.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('governorate.name_en') is-invalid @enderror" id="name_en"
                   name="name"
                   wire:model.defer="governorate.name_en">
            @error('governorate.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_ar">{{__('pages/countries/governorates/index.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('governorate.name_ar') is-invalid @enderror" id="name_ar"
                   name="name"
                   wire:model.defer="governorate.name_ar">
            @error('governorate.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
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
