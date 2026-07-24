<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$business_type['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="name_en">{{__('pages/advertisers/business-types/index.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('business_type.name_en') is-invalid @enderror" id="name_en"
                   wire:model.defer="business_type.name_en"
                   >
            @error('business_type.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_ar">{{__('pages/advertisers/business-types/index.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('business_type.name_ar') is-invalid @enderror" id="name_ar"
                   wire:model.defer="business_type.name_ar"
                   >
            @error('business_type.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="is_active">{{__('pages/advertisers/business-types/index.modal.edit.inputs.is_active')}}</label>
            <select class="form-control @error('business_type.is_active') is-invalid @enderror" id="is_active" wire:model.defer="business_type.is_active">
                <option value="1">{{__('pages/advertisers/business-types/index.modal.edit.inputs.boolean.yes')}}</option>
                <option value="0">{{__('pages/advertisers/business-types/index.modal.edit.inputs.boolean.no')}}</option>
            </select>
            @error('business_type.is_active')
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
