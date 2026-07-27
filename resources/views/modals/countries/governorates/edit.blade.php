<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$governorate['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
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
