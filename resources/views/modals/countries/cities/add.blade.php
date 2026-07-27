<!-- Add Items Confirmation Modal -->
<x-form-modal wire:model="showAddModal" type="add" wire="store">
    <x-slot name="title">
        {{__('pages/countries/cities/create.content.title')}}
    </x-slot>
    <x-slot name="content">
        @if(!$governorate_id)
            <div class="form-group">
                <label for="city_governorate_id">{{__('pages/countries/cities/index.content.datatable.governorate')}}</label>
                <select class="form-control @error('city.governorate_id') is-invalid @enderror"
                        id="city_governorate_id"
                        wire:model.defer="city.governorate_id">
                    <option value="">{{__('pages/countries/cities/create.content.inputs.governorate')}}</option>
                    @foreach(\App\Models\Countries\Governorates\Governorate::orderBy('country_code')->orderBy('order')->get() as $governorate)
                        <option value="{{ $governorate->id }}">
                            {{ app()->getLocale() === 'ar' ? $governorate->name_ar : $governorate->name_en }}
                        </option>
                    @endforeach
                </select>
                @error('city.governorate_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        @endif
        <div class="form-group">
            <label for="city_name_en">{{__('pages/countries/cities/create.content.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('city.name_en') is-invalid @enderror" id="city_name_en"
                   name="name" wire:model.defer="city.name_en">
            @error('city.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="city_name_ar">{{__('pages/countries/cities/create.content.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('city.name_ar') is-invalid @enderror" id="city_name_ar"
                   name="name"
                   wire:model.defer="city.name_ar">
            @error('city.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeAddModal" wire:loading.attr="disabled">
            {{__('pages/countries/cities/create.content.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/countries/cities/create.content.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
