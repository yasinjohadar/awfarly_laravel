<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name">{{__('pages/customers/create.content.inputs.name')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       name="name" wire:model.defer="name"
                       >
                @error('name')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="email">{{__('pages/customers/create.content.inputs.email')}}</label>
            <div class="col-lg-10">
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                       name="email" wire:model.defer="email"
                       />
                @error('email')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="mobile">{{__('pages/customers/create.content.inputs.mobile')}}</label>
            <div class="col-lg-10">
                <input type="text" dir="ltr" class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('mobile') is-invalid @enderror" id="mobile"
                       name="mobile" wire:model.defer="mobile"
                       />
                @error('mobile')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row"
             x-data="{ isUploading: false, progress: 0, isUploaded: false }"
             x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
             x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
             x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label for="image"
                   class="col-form-label col-lg-2">{{__('pages/customers/create.content.inputs.placeholders.choose_file')}}</label>
            <div class="col-lg-10">
                <input type="file" wire:model.defer="image" class="form-control h-auto" id="image">
                <!-- Progress Bar -->
                <div x-show="isUploading">
                    <progress max="100" x-bind:value="progress"></progress>
                </div>
                @if($image)
                    <div x-show="isUploaded">
                        <img alt="{{$image}}" class="img-fluid mt-2" width="240"
                             src="{{ $image->temporaryUrl() }}">
                    </div>
                @endif
                @error('image') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="username">{{__('pages/customers/create.content.inputs.username')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('username') is-invalid @enderror" id="username"
                       name="username" wire:model.defer="username"
                       >
                @error('username')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="password">{{__('pages/customers/create.content.inputs.password')}}</label>
            <div class="col-lg-10">
                <input class="form-control" id="password" name="password" wire:model.defer="password"
                       >
            </div>
        </div>
        <div class="form-group row" wire:ignore
             x-data="{country_code: @entangle('country_code').defer, countries: {{json_encode($countries)}},}"
             x-init="$nextTick(() => {
                 select2 = $($refs.country_code).select2({
                        placeholder: '{{__('pages/customers/create.content.inputs.placeholders.country')}}',
                        cache: true
                    }).val('').change();
                    select2.on('change', (event) => {
                        country_code = event.target.value;
                        $dispatch('governorates', {country_code: (country_code !== 'none') ? country_code : null})
                        $dispatch('select-governorate', {country_code: (country_code !== 'none') ? country_code : null})
                    });
                })">
            <label class="col-form-label col-lg-2"
                   for="country_code">{{__('pages/customers/create.content.inputs.country')}}</label>
            <div class="col-lg-10">
                <select x-model="country_code" x-cloak
                        data-placeholder="{{__('pages/customers/create.content.inputs.placeholders.country')}}"
                        id="country_code"
                        x-ref="country_code"
                        x-bind:value="country_code" class="form-control @error('country_code') is-invalid @enderror"
                        wire:model.defer="country_code">
                    <option></option>
                    <template x-for="[id, value] in Object.entries(countries)">
                        <option :value="id" x-text="value.value"></option>
                    </template>
                </select>
            </div>
        </div>
        @error('country_code')
        <div class="form-group row" style="margin-top: -20px">
            <div class="col-form-label col-lg-2"></div>
            <div class="col-lg-10">
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
            </div>
        </div>
        @enderror
        <div x-subscribe="country_code" wire:ignore
             x-data="{add: false, country_code: null}"
             x-on:governorates.window="country_code = $event.detail.country_code"
             x-init="country_code = null">
            <template x-if="country_code">
                <div class="form-group row" x-data="{governorate_id: @entangle('governorate_id').defer}"
                     x-init="$nextTick(() => {
                        $('#governorate_id').select2().on('change', (event) => {
                            governorate_id = $('#governorate_id').val();
                            $dispatch('cities', {governorate_id: governorate_id});
                            $dispatch('select-city', {governorate_id: governorate_id});
                        });
                        if (governorate_id && governorate_id !== 'none') {
                            $dispatch('cities', {governorate_id: governorate_id});
                            $dispatch('select-city', {governorate_id: governorate_id});
                        }
                    })">
                    <label class="col-form-label col-lg-2"
                           for="governorate_id">{{__('pages/customers/create.content.inputs.governorate')}}</label>
                    <div class="col-lg-10">
                        <select x-cloak x-model="governorate_id" name="governorate_id"
                                data-placeholder="{{__('pages/customers/create.content.inputs.placeholders.governorate')}}"
                                id="governorate_id"
                                class="form-control select2 @error('governorate_id') is-invalid @enderror"
                                x-ref="governorate_id"
                                x-bind:value="governorate_id">
                            <option></option>
                        </select>
                    </div>
                </div>
            </template>
        </div>
        @error('governorate_id')
        <div class="form-group row" style="margin-top: -20px">
            <div class="col-form-label col-lg-2"></div>
            <div class="col-lg-10">
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
            </div>
        </div>
        @enderror
        <div x-subscribe="governorate_id" wire:ignore
             x-data="{add: false, governorate_id: null}"
             x-on:cities.window="governorate_id = $event.detail.governorate_id"
             x-init="governorate_id = null">
            <template x-if="governorate_id">
                <div class="form-group row" x-data="{city_id: @entangle('city_id').defer}"
                     x-init="$nextTick(() => {
                        $('#city_id').select2().on('change', (event) => {
                            city_id = $('#city_id').val();
                        })
                    })">
                    <label class="col-form-label col-lg-2"
                           for="city_id">{{__('pages/customers/create.content.inputs.city')}}</label>
                    <div class="col-lg-10">
                        <select x-cloak x-model="city_id" name="city_id"
                                data-placeholder="{{__('pages/customers/create.content.inputs.placeholders.city')}}"
                                id="city_id"
                                class="form-control select2 @error('city_id') is-invalid @enderror"
                                x-ref="city_id"
                                x-bind:value="city_id">
                            <option></option>
                        </select>
                    </div>
                </div>
            </template>
        </div>
        @error('city_id')
        <div class="form-group row" style="margin-top: -20px">
            <div class="col-form-label col-lg-2"></div>
            <div class="col-lg-10">
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
            </div>
        </div>
        @enderror
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="language">{{__('pages/customers/create.content.inputs.language')}}</label>
            <div class="col-lg-10">
                <select class="form-control @error('language_code') is-invalid @enderror"
                        wire:model.defer="language_code"
                        id="language">
                    <option
                        value="none">{{__('pages/customers/create.content.inputs.placeholders.language')}}</option>
                    @foreach($languages as $index => $language)
                        <option value="{{$index}}">{{$language}}</option>
                    @endforeach
                </select>
                @error('language_code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="contact_number">{{__('pages/customers/create.content.inputs.contact_number')}}</label>
            <div class="col-lg-10">
                <input dir="ltr" class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('contact_number') is-invalid @enderror" id="contact_number"
                       name="contact_number" wire:model.defer="contact_number"
                       >
                @error('contact_number')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="whatsapp_number">{{__('pages/customers/create.content.inputs.whatsapp_number')}}</label>
            <div class="col-lg-10">
                <input dir="ltr" class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('whatsapp_number') is-invalid @enderror" id="whatsapp_number"
                       name="whatsapp_number" wire:model.defer="whatsapp_number"
                       >
                @error('whatsapp_number')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="facebook_url">{{__('pages/customers/create.content.inputs.facebook_url')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('facebook_url') is-invalid @enderror" id="facebook_url"
                       name="facebook_url" wire:model.defer="facebook_url"
                       >
                @error('facebook_url')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="twitter_url">{{__('pages/customers/create.content.inputs.twitter_url')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('twitter_url') is-invalid @enderror" id="twitter_url"
                       name="twitter_url" wire:model.defer="twitter_url"
                       >
                @error('twitter_url')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="website_url">{{__('pages/customers/create.content.inputs.website_url')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('website_url') is-invalid @enderror" id="website_url"
                       name="website_url" wire:model.defer="website_url"
                       >
                @error('website_url')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="status">{{__('pages/customers/create.content.inputs.status')}}</label>
            <div class="col-lg-10">
                <select class="form-control @error('status') is-invalid @enderror"
                        wire:model.defer="status"
                        id="status">
                    <option value="active">{{__('pages/customers/create.content.inputs.status_options.active')}}</option>
                    <option value="inactive">{{__('pages/customers/create.content.inputs.status_options.inactive')}}</option>
                    <option value="banned">{{__('pages/customers/create.content.inputs.status_options.banned')}}</option>
                </select>
                @error('status')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="is_accepted_send_notification">{{__('pages/customers/create.content.inputs.accepted_send_notification')}}</label>
            <div class="col-lg-10">
                <select class="form-control @error('is_accepted_send_notification') is-invalid @enderror"
                        wire:model.defer="is_accepted_send_notification"
                        id="is_accepted_send_notification">
                    <option value="1">{{__('pages/customers/create.content.inputs.boolean.yes')}}</option>
                    <option value="0">{{__('pages/customers/create.content.inputs.boolean.no')}}</option>
                </select>
                @error('is_accepted_send_notification')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/customers/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#image').val(null);
            $('#country_code').select2().val(null).change();
            $('#governorate_id').select2().val(null).change();
            $('#city_id').select2().val(null).change();
        });
        window.addEventListener('select-governorate', (el) => {
            axios.get('{{route('admin.country.governorates')}}', {
                params: {
                    country_code: el.detail.country_code,
                }
            }).then(function (response) {
                $('#governorate_id').children('option').remove();
                $('#governorate_id').select2({
                    placeholder: '{{__('pages/customers/create.content.inputs.placeholders.governorate')}}',
                    data: response.data,
                }).val('').change();
                $('#city_id').children('option').remove();
                $('#city_id').select2().val('').change();
            })
        });
        window.addEventListener('select-city', (el) => {
            axios.get('{{route('admin.governorate.cities')}}', {
                params: {
                    governorate_id: el.detail.governorate_id,
                }
            }).then(function (response) {
                $('#city_id').children('option').remove();
                $('#city_id').select2({
                    placeholder: '{{__('pages/customers/create.content.inputs.placeholders.city')}}',
                    data: response.data,
                }).val('').change();
            })
        });
    </script>
@endpush
