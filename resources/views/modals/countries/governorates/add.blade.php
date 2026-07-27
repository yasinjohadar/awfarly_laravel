<!-- Add Items Confirmation Modal -->
<x-form-modal wire:model="showAddModal" type="add" wire="store">
    <x-slot name="title">
        {{__('pages/countries/governorates/create.content.title')}}
    </x-slot>
    <x-slot name="content">
        @if(!$country_id)
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
        @endif
        <div class="form-group">
            <label for="governorate_name_en">{{__('pages/countries/governorates/create.content.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('governorate.name_en') is-invalid @enderror" id="governorate_name_en"
                   name="name" wire:model.defer="governorate.name_en">
            @error('governorate.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="governorate_name_ar">{{__('pages/countries/governorates/create.content.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('governorate.name_ar') is-invalid @enderror" id="governorate_name_ar"
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
        <x-secondary-button wire:click="closeAddModal" wire:loading.attr="disabled">
            {{__('pages/countries/governorates/create.content.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/countries/governorates/create.content.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
