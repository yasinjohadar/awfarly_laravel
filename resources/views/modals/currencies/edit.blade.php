<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$currency['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="code">{{__('pages/currencies/index.modal.edit.inputs.code')}}</label>
            <input type="text" class="form-control @error('currency.code') is-invalid @enderror" id="code"
                   name="code" wire:model.defer="currency.code" style="text-transform:uppercase">
            @error('currency.code')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_en">{{__('pages/currencies/index.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('currency.name_en') is-invalid @enderror" id="name_en"
                   name="name_en" wire:model.defer="currency.name_en">
            @error('currency.name_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="name_ar">{{__('pages/currencies/index.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('currency.name_ar') is-invalid @enderror" id="name_ar"
                   name="name_ar" wire:model.defer="currency.name_ar">
            @error('currency.name_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="symbol">{{__('pages/currencies/index.modal.edit.inputs.symbol')}}</label>
            <input type="text" class="form-control @error('currency.symbol') is-invalid @enderror" id="symbol"
                   name="symbol" wire:model.defer="currency.symbol">
            @error('currency.symbol')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="exchange_rate">{{__('pages/currencies/index.modal.edit.inputs.exchange_rate')}}</label>
            <input type="number" min="0.000001" step="0.000001"
                   class="form-control @error('currency.exchange_rate') is-invalid @enderror" id="exchange_rate"
                   name="exchange_rate" wire:model.defer="currency.exchange_rate"
                   @if(isset($currency['is_base']) && $currency['is_base']) disabled @endif>
            @if(isset($currency['is_base']) && $currency['is_base'])
                <div class="form-control-plaintext p-0">
                    {{__('pages/currencies/index.modal.edit.inputs.base_rate_locked')}}
                </div>
            @endif
            @error('currency.exchange_rate')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="is_base">{{__('pages/currencies/index.modal.edit.inputs.is_base')}}</label>
            <select class="form-control" wire:model.defer="currency.is_base" id="is_base">
                <option value="1">{{__('pages/currencies/index.modal.edit.inputs.boolean.yes')}}</option>
                <option value="0">{{__('pages/currencies/index.modal.edit.inputs.boolean.no')}}</option>
            </select>
            @error('currency.is_base')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group">
            <label for="is_active">{{__('pages/currencies/index.modal.edit.inputs.is_active')}}</label>
            <select class="form-control" wire:model.defer="currency.is_active" id="is_active">
                <option value="1">{{__('pages/currencies/index.modal.edit.inputs.boolean.yes')}}</option>
                <option value="0">{{__('pages/currencies/index.modal.edit.inputs.boolean.no')}}</option>
            </select>
            @error('currency.is_active')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group">
            <label for="is_visible">{{__('pages/currencies/index.modal.edit.inputs.is_visible')}}</label>
            <select class="form-control" wire:model.defer="currency.is_visible" id="is_visible">
                <option value="1">{{__('pages/currencies/index.modal.edit.inputs.boolean.yes')}}</option>
                <option value="0">{{__('pages/currencies/index.modal.edit.inputs.boolean.no')}}</option>
            </select>
            @error('currency.is_visible')
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
