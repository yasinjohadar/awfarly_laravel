<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$payment['id'] ?? null}})">
    <x-slot name="title">
        {{ __('pages/subscriptions/payments/show.modal.edit.title') }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group row">
            <label for="starts_at">{{__('pages/subscriptions/payments/show.modal.edit.inputs.starts_at')}}</label>
            <input type="datetime-local" class="form-control @error('payment.starts_at') is-invalid @enderror" id="starts_at"
                   wire:model.defer="payment.starts_at"/>
            @error('payment.starts_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="ends_at">{{__('pages/subscriptions/payments/show.modal.edit.inputs.ends_at')}}</label>
            <input type="datetime-local" class="form-control @error('payment.ends_at') is-invalid @enderror" id="ends_at"
                   wire:model.defer="payment.ends_at"/>
            @error('payment.ends_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="is_ended">{{__('pages/subscriptions/payments/show.modal.edit.inputs.is_ended')}}</label>
            <select class="form-control @error('payment.is_ended') is-invalid @enderror" wire:model.defer="payment.is_ended"
                    id="is_ended">
                <option value="1">
                    {{__('pages/subscriptions/payments/show.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/subscriptions/payments/show.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('payment.is_ended')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="is_active">{{__('pages/subscriptions/payments/show.modal.edit.inputs.is_active')}}</label>
            <select class="form-control @error('payment.is_active') is-invalid @enderror" wire:model.defer="payment.is_active"
                    id="is_active">
                <option value="1">
                    {{__('pages/subscriptions/payments/show.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/subscriptions/payments/show.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('payment.is_active')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="is_current">{{__('pages/subscriptions/payments/show.modal.edit.inputs.is_current')}}</label>
            <select class="form-control @error('payment.is_current') is-invalid @enderror" wire:model.defer="payment.is_current"
                    id="is_current">
                <option value="1">
                    {{__('pages/subscriptions/payments/show.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/subscriptions/payments/show.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('payment.is_current')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{ __('pages/subscriptions/payments/show.modal.edit.cancel') }}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{ __('pages/subscriptions/payments/show.modal.edit.submit') }}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
