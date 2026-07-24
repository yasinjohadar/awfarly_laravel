<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$city['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="name_en">{{__('pages/countries/cities/index.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('city.name_en') is-invalid @enderror" id="name_en"
                   name="name"
                   wire:model.defer="city.name_en"
                   >
            @error('city.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_ar">{{__('pages/countries/cities/index.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('city.name_ar') is-invalid @enderror" id="name_ar"
                   name="name"
                   wire:model.defer="city.name_ar"
                   >
            @error('city.name_ar')
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
<!-- /Edit Items Confirmation Modal -->
