<!-- Add Items Confirmation Modal -->
<x-form-modal wire:model="showAddModal" type="add" wire="store">
    <x-slot name="title">
        {{__('pages/countries/cities/create.content.title')}}
    </x-slot>
    <x-slot name="content">
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
<!-- /Add Items Confirmation Modal -->

