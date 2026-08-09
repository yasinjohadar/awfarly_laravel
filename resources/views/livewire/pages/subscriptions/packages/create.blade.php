<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="row">
            <div class="form-group col-md-6">
                <label for="name_en">{{__('pages/subscriptions/packages/create.content.inputs.name_en')}}</label>
                <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en"
                       name="name_en" wire:model.defer="name_en"/>
                @error('name_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="name_ar">{{__('pages/subscriptions/packages/create.content.inputs.name_ar')}}</label>
                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar"
                       name="name_ar" wire:model.defer="name_ar"/>
                @error('name_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="description_en">{{__('pages/subscriptions/packages/create.content.inputs.description_en')}}</label>
                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en"
                          name="description_en" wire:model.defer="description_en">
                </textarea>
                @error('description_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="description_ar">{{__('pages/subscriptions/packages/create.content.inputs.description_ar')}}</label>
                <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar"
                          name="description_ar" wire:model.defer="description_ar">
                </textarea>
                @error('description_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="specifications_en">{{__('pages/subscriptions/packages/create.content.inputs.specifications_en')}}</label>
                <textarea class="form-control @error('specifications_en') is-invalid @enderror" id="specifications_en"
                          wire:model.defer="specifications_en">
                </textarea>
                @error('specifications_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="specifications_ar">{{__('pages/subscriptions/packages/create.content.inputs.specifications_ar')}}</label>
                <textarea class="form-control @error('specifications_ar') is-invalid @enderror" id="specifications_ar"
                          wire:model.defer="specifications_ar">
                </textarea>
                @error('specifications_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="product_id">{{__('pages/subscriptions/packages/create.content.inputs.product_id')}}</label>
                <input type="text" class="form-control @error('product_id') is-invalid @enderror" id="product_id"
                       name="product_id" wire:model.defer="product_id"/>
                @error('product_id')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="subscription_type">{{__('pages/subscriptions/packages/create.content.inputs.subscription_type')}}</label>
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
        <div class="row">
            <div class="form-group col-md-6">
                <label for="maximum_posts">{{__('pages/subscriptions/packages/create.content.inputs.maximum_posts')}}</label>
                <input type="number" min="0" class="form-control @error('maximum_posts') is-invalid @enderror"
                       id="maximum_posts" wire:model.defer="maximum_posts"/>
                @error('maximum_posts')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="maximum_offers">{{__('pages/subscriptions/packages/create.content.inputs.maximum_offers')}}</label>
                <input type="number" min="0" class="form-control @error('maximum_offers') is-invalid @enderror"
                       id="maximum_offers" wire:model.defer="maximum_offers"/>
                @error('maximum_offers')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="maximum_monthly_offers">{{__('pages/subscriptions/packages/create.content.inputs.maximum_monthly_offers')}}</label>
                <input type="number" min="0" class="form-control @error('maximum_monthly_offers') is-invalid @enderror"
                       id="maximum_monthly_offers" wire:model.defer="maximum_monthly_offers"/>
                @error('maximum_monthly_offers')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="currency">{{__('pages/subscriptions/packages/create.content.inputs.currency')}}</label>
                <select class="form-control" wire:model.defer="currency" id="currency">
                    @foreach($currencies as $currencyOption)
                        <option value="{{$currencyOption->code}}">
                            {{(App::getLocale() === 'ar') ? $currencyOption->name_ar : $currencyOption->name_en}} ({{$currencyOption->code}})
                        </option>
                    @endforeach
                </select>
                @error('currency')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="price">{{__('pages/subscriptions/packages/create.content.inputs.price')}}</label>
                <input type="number" min="0" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price"
                       wire:model.defer="price"/>
                @error('price')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="old_price">{{__('pages/subscriptions/packages/create.content.inputs.old_price')}}</label>
                <input type="number" min="0" step="0.01" class="form-control @error('old_price') is-invalid @enderror"
                       id="old_price" wire:model.defer="old_price"/>
                @error('old_price')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        {{--<div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="duration">{{__('pages/subscriptions/packages/create.content.inputs.duration')}}</label>
            <div class="col-lg-11">
                <input type="number" min="0" class="form-control @error('duration') is-invalid @enderror"
                       id="duration" wire:model.defer="duration" required/>
                @error('duration')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>--}}
        <div class="row">
            <div class="form-group col-md-6">
                <label for="is_visible">{{__('pages/subscriptions/packages/create.content.inputs.is_visible')}}</label>
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
            <div class="form-group col-md-6">
                <label for="is_active">{{__('pages/subscriptions/packages/create.content.inputs.is_active')}}</label>
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
        <div class="row">
            <div class="form-group col-md-6">
                <label for="is_trial">{{__('pages/subscriptions/packages/create.content.inputs.is_trial')}}</label>
                <select class="form-control" wire:model.defer="is_trial" id="is_trial">
                    <option value="1">
                        {{__('pages/subscriptions/packages/create.content.inputs.boolean.yes')}}
                    </option>
                    <option value="0">
                        {{__('pages/subscriptions/packages/create.content.inputs.boolean.no')}}
                    </option>
                </select>
                @error('is_trial')
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
