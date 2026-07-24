<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$package['id'] ?? null}})">
    <x-slot name="title">
        {{ __('pages/subscriptions/packages/show.modal.edit.title') }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group row">
            <label for="product_id">{{__('pages/subscriptions/packages/show.modal.edit.inputs.product_id')}}</label>
            <input type="text" class="form-control @error('product_id') is-invalid @enderror" id="product_id"
                   name="product_id" wire:model.defer="product_id"/>
            @error('product_id')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="name_en">{{__('pages/subscriptions/packages/show.modal.edit.inputs.name_en')}}</label>
            <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en"
                   name="name_en" wire:model.defer="name_en"/>
            @error('name_en')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="name_ar">{{__('pages/subscriptions/packages/show.modal.edit.inputs.name_ar')}}</label>
            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar"
                   name="name_ar" wire:model.defer="name_ar"/>
            @error('name_ar')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label
                for="description_en">{{__('pages/subscriptions/packages/show.modal.edit.inputs.description_en')}}</label>
            <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en"
                      name="description_en" wire:model.defer="description_en">
                </textarea>
            @error('description_en')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label
                for="description_ar">{{__('pages/subscriptions/packages/show.modal.edit.inputs.description_ar')}}</label>
            <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar"
                      name="description_ar" wire:model.defer="description_ar">
                </textarea>
            @error('description_ar')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="specifications_en">
                {{__('pages/subscriptions/packages/show.modal.edit.inputs.specifications_en')}}
            </label>
            <textarea class="form-control @error('specifications_en') is-invalid @enderror" id="specifications_en"
                      wire:model.defer="specifications_en">
                </textarea>
            @error('specifications_en')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="specifications_ar">
                {{__('pages/subscriptions/packages/show.modal.edit.inputs.specifications_ar')}}
            </label>
            <textarea class="form-control @error('specifications_ar') is-invalid @enderror" id="specifications_ar"
                      wire:model.defer="specifications_ar">
                </textarea>
            @error('specifications_ar')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="maximum_posts">
                {{__('pages/subscriptions/packages/show.modal.edit.inputs.maximum_posts')}}
            </label>
            <input type="number" min="0" class="form-control @error('maximum_posts') is-invalid @enderror"
                   id="maximum_posts" wire:model.defer="maximum_posts"/>
            @error('maximum_posts')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="form-group row">
            <label for="maximum_posts">
                {{__('pages/subscriptions/packages/show.modal.edit.inputs.maximum_offers')}}
            </label>
            <input type="number" min="0" class="form-control @error('maximum_offers') is-invalid @enderror"
                   id="maximum_offers" wire:model.defer="maximum_offers"/>
            @error('maximum_offers')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="price">{{__('pages/subscriptions/packages/show.modal.edit.inputs.price')}}</label>
            <input type="number" min="0" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price"
                   wire:model.defer="price"/>
            @error('price')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="old_price">
                {{__('pages/subscriptions/packages/show.modal.edit.inputs.old_price')}}
            </label>
            <input type="number" min="0" step="0.01" class="form-control @error('old_price') is-invalid @enderror"
                   id="old_price" wire:model.defer="old_price"/>
            @error('old_price')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="subscription_type">
                {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_type')}}
            </label>
            <select class="form-control" wire:model.defer="subscription_type" id="subscription_type">
                {{--<option value="minutely">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.minutely')}}
                </option>
                <option value="hourly">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.hourly')}}
                </option>--}}
                <option value="daily">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.daily')}}
                </option>
                <option value="weekly">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.weekly')}}
                </option>
                <option value="monthly">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.monthly')}}
                </option>
                <option value="two_months">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.two_months')}}
                </option>
                <option value="three_months">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.three_months')}}
                </option>
                <option value="six_months">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.six_months')}}
                </option>
                <option value="yearly">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.subscription_types.yearly')}}
                </option>
            </select>
            @error('subscription_type')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="currency">{{__('pages/subscriptions/packages/show.modal.edit.inputs.currency')}}</label>
            <select class="form-control" wire:model.defer="currency" id="currency">
                <option value="SAR">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.currencies.SAR')}}
                </option>
                {{--<option value="USD">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.currencies.USD')}}
                </option>--}}
            </select>
            @error('currency')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        {{--<div class="form-group row">
            <label for="duration">{{__('pages/subscriptions/packages/show.modal.edit.inputs.duration')}}</label>
            <input type="number" min="0" class="form-control @error('duration') is-invalid @enderror"
                   id="duration" wire:model.defer="duration" required/>
            @error('duration')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>--}}
        <div class="form-group row">
            <label for="is_visible">{{__('pages/subscriptions/packages/show.modal.edit.inputs.is_visible')}}</label>
            <select class="form-control @error('is_visible') is-invalid @enderror" wire:model.defer="is_visible"
                    id="is_visible">
                <option value="1">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('is_visible')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="is_active">{{__('pages/subscriptions/packages/show.modal.edit.inputs.is_active')}}</label>
            <select class="form-control @error('is_active') is-invalid @enderror" wire:model.defer="is_active"
                    id="is_active">
                <option value="1">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('is_active')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group row">
            <label for="is_trial">{{__('pages/subscriptions/packages/show.modal.edit.inputs.is_trial')}}</label>
            <select class="form-control @error('is_trial') is-invalid @enderror" wire:model.defer="is_trial"
                    id="is_trial">
                <option value="1">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.boolean.yes')}}
                </option>
                <option value="0">
                    {{__('pages/subscriptions/packages/show.modal.edit.inputs.boolean.no')}}
                </option>
            </select>
            @error('is_trial')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{ __('pages/subscriptions/packages/show.modal.edit.cancel') }}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{ __('pages/subscriptions/packages/show.modal.edit.submit') }}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
