<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="name">{{__('pages/subscriptions/packages/create.content.inputs.name')}}</label>
            <div class="col-lg-11">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       name="name" wire:model.defer="name"/>
                @error('name')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="description">{{__('pages/subscriptions/packages/create.content.inputs.description')}}</label>
            <div class="col-lg-11">
                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                          name="description" wire:model.defer="description">
                </textarea>
                @error('description')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="specifications">{{__('pages/subscriptions/packages/create.content.inputs.specifications')}}</label>
            <div class="col-lg-11">
                <textarea class="form-control @error('specifications') is-invalid @enderror" id="specifications"
                          wire:model.defer="specifications">
                </textarea>
                @error('specifications')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="maximum_posts">{{__('pages/subscriptions/packages/create.content.inputs.maximum_posts')}}</label>
            <div class="col-lg-11">
                <input type="number" min="0" class="form-control @error('maximum_posts') is-invalid @enderror"
                       id="maximum_posts" wire:model.defer="maximum_posts"/>
                @error('maximum_posts')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="price">{{__('pages/subscriptions/packages/create.content.inputs.price')}}</label>
            <div class="col-lg-11">
                <input type="number" min="0" class="form-control @error('price') is-invalid @enderror" id="price"
                       wire:model.defer="price"/>
                @error('price')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="old_price">{{__('pages/subscriptions/packages/create.content.inputs.old_price')}}</label>
            <div class="col-lg-11">
                <input type="number" min="0" class="form-control @error('old_price') is-invalid @enderror"
                       id="old_price" wire:model.defer="old_price"/>
                @error('old_price')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="subscription_type">{{__('pages/subscriptions/packages/create.content.inputs.subscription_type')}}</label>
            <div class="col-lg-11">
                <select class="form-control" wire:model.defer="subscription_type" id="subscription_type">
                    {{--<option value="minutely">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.minutely')}}
                    </option>
                    <option value="hourly">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.hourly')}}
                    </option>--}}
                    <option value="daily">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.daily')}}
                    </option>
                    <option value="weekly">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.weekly')}}
                    </option>
                    <option value="monthly">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.monthly')}}
                    </option>
                    <option value="two_months">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.two_months')}}
                    </option>
                    <option value="three_months">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.three_months')}}
                    </option>
                    <option value="six_months">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.six_months')}}
                    </option>
                    <option value="yearly">
                        {{__('pages/subscriptions/packages/create.content.inputs.subscription_types.yearly')}}
                    </option>
                </select>
                @error('subscription_type')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="currency">{{__('pages/subscriptions/packages/create.content.inputs.currency')}}</label>
            <div class="col-lg-11">
                <select class="form-control" wire:model.defer="currency" id="currency">
                    <option value="SAR">
                        {{__('pages/subscriptions/packages/create.content.inputs.currencies.SAR')}}
                    </option>
                    <option value="USD">
                        {{__('pages/subscriptions/packages/create.content.inputs.currencies.USD')}}
                    </option>
                </select>
                @error('currency')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="duration">{{__('pages/subscriptions/packages/create.content.inputs.duration')}}</label>
            <div class="col-lg-11">
                <input type="number" min="0" class="form-control @error('duration') is-invalid @enderror"
                       id="duration" wire:model.defer="duration"/>
                @error('duration')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="is_visible">{{__('pages/subscriptions/packages/create.content.inputs.is_visible')}}</label>
            <div class="col-lg-11">
                <select class="form-control" wire:model.defer="is_visible" id="is_visible">
                    <option value="1">
                        {{__('pages/subscriptions/packages/create.content.inputs.boolean.yes')}}
                    </option>
                    <option value="0">
                        {{__('pages/subscriptions/packages/create.content.inputs.boolean.no')}}
                    </option>
                </select>
                @error('is_visible')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="is_active">{{__('pages/subscriptions/packages/create.content.inputs.is_active')}}</label>
            <div class="col-lg-11">
                <select class="form-control" wire:model.defer="is_active" id="is_active">
                    <option value="1">
                        {{__('pages/subscriptions/packages/create.content.inputs.boolean.yes')}}
                    </option>
                    <option value="0">
                        {{__('pages/subscriptions/packages/create.content.inputs.boolean.no')}}
                    </option>
                </select>
                @error('is_active')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/subscriptions/packages/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>
