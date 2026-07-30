<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$user['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <form wire:submit.prevent="update({{$user['id'] ?? null}})">
        <x-slot name="content">
            <div class="form-group">
                <label for="name">{{__('pages/advertisers/index.modal.edit.inputs.name')}}</label>
                <input type="text" class="form-control @error('user.name') is-invalid @enderror" id="name" name="name"
                       wire:model.defer="user.name">
                @error('user.name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group" wire:ignore
                 x-on:change-business.window="business_type = $event.detail;
                 $('#business_type').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.business_type')}}',
                        cache: true,
                    }).val(business_type).change()"
                 x-data="{business_type: @entangle('business_type').defer,}"
                 x-init="$nextTick(() => {
                 select2 = $('#business_type').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.business_type')}}',
                        cache: true,
                    }).val(business_type).change();
                    select2.on('select2:select', (event) => {
                        business_type = event.target.value;
                    });
                })">
                <label for="business_type">{{__('pages/advertisers/index.modal.edit.inputs.business_type')}}</label>
                <select x-model="business_type" x-cloak
                        data-placeholder="{{__('pages/advertisers/index.modal.edit.inputs.placeholders.business_type')}}"
                        id="business_type"
                        x-ref="business_type"
                        x-bind:value="business_type"
                        class="form-control @error('user.business_type') is-invalid @enderror">
                    <option></option>
                    @foreach ($business_types as $business_type)
                        <option value="{{$business_type['id']}}">{{$business_type['name']}}</option>
                    @endforeach
                </select>
                @error('user.business_type')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group" wire:ignore
                 x-on:change-package-id.window="package_id = $event.detail;
                 $('#package_id').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.package')}}',
                        allowClear: true,
                        cache: true,
                    }).val(package_id).change()"
                 x-data="{package_id: @entangle('package_id').defer,}"
                 x-init="$nextTick(() => {
                 select2 = $('#package_id').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.package')}}',
                        cache: true,
                        allowClear: true,
                    }).val(package_id).change();
                    select2.on('change', (event) => {
                        package_id = event.target.value;
                    });
                })">
                <label for="package_id">{{__('pages/advertisers/index.modal.edit.inputs.package')}}</label>
                <select x-model="package_id" x-cloak
                        data-placeholder="{{__('pages/advertisers/index.modal.edit.inputs.placeholders.package')}}"
                        id="package_id"
                        x-ref="package_id"
                        x-bind:value="package_id"
                        class="form-control @error('package_id') is-invalid @enderror">
                    <option></option>
                    @foreach ($packages as $package)
                        <option value="{{$package['id']}}">{{$package['name']}}</option>
                    @endforeach
                </select>
                @if(isset($user['package_id']) && $user['package_id'])
                    <div class="form-control-feedback">
                        <strong>{{__('pages/advertisers/index.modal.edit.inputs.package_notes')}}</strong>
                    </div>
                @endif
                @error('user.package_id')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">{{__('pages/advertisers/index.modal.edit.inputs.email')}}</label>
                <input type="email" class="form-control @error('user.email') is-invalid @enderror" id="email"
                       name="email" wire:model.defer="user.email">
                @error('user.email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="mobile">{{__('pages/advertisers/index.modal.edit.inputs.mobile')}}</label>
                <input dir="ltr" type="text" class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('user.mobile') is-invalid @enderror" id="mobile"
                       name="mobile" wire:model.defer="user.mobile">
                @error('user.mobile')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="username">{{__('pages/advertisers/index.modal.edit.inputs.username')}}</label>
                <input class="form-control @error('user.username') is-invalid @enderror" id="username" name="username"
                       wire:model.defer="user.username">
                @error('user.username')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">{{__('pages/advertisers/index.modal.edit.inputs.password')}}</label>
                <input class="form-control" id="password" name="password" wire:model.defer="user.password">
                <div
                    class="text-danger small">{{__('pages/advertisers/index.modal.edit.inputs.placeholders.password')}}</div>
            </div>
            <div class="form-group">
                <label for="bio">{{__('pages/advertisers/index.modal.edit.inputs.bio')}}</label>
                <textarea class="form-control" id="bio" name="bio" wire:model.defer="user.bio"></textarea>
            </div>
            <div class="form-group"
                 x-data="{ isUploading: false, progress: 0, isUploaded: false }"
                 x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
                 x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
                 x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <label for="new_image">{{__('pages/advertisers/index.modal.edit.inputs.image')}}</label>
                <input type="file" wire:model.defer="user.new_image" class="form-control h-auto" id="new_image">
                <!-- Progress Bar -->
                <div x-show="isUploading">
                    <progress max="100" x-bind:value="progress"></progress>
                </div>
                @error('user.new_image') <span class="error">{{ $message }}</span> @enderror
                @isset($user['new_image'])
                    <img alt="{{$user['new_image']}}" class="img-fluid mt-2" width="240"
                         src="{{ $user['new_image']->temporaryUrl() }}">
                @endisset
            </div>
            <div class="form-group"
                 wire:ignore
                 x-on:change-country.window="country_code = $event.detail.country_code, governorate_id = $event.detail.governorate_id, city_id = $event.detail.city_id;
                 $('#country_code').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.country')}}',
                        cache: true
                    }).val(country_code).change();
                    $dispatch('governorates', {country_code: (country_code !== 'none') ? country_code : null});
                    if (governorate_id && governorate_id !== 'none') {
                        $dispatch('cities', {governorate_id: governorate_id});
                    }
                    axios.get('{{route('admin.country.governorates')}}', {
                        params: { country_code: country_code }
                    }).then(function (response) {
                        $('#governorate_id').children('option').remove();
                        $('#governorate_id').select2({
                            placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.governorate')}}',
                            data: response.data,
                        }).val(governorate_id).change();
                        if (!governorate_id || governorate_id === 'none') {
                            return null;
                        }
                        return axios.get('{{route('admin.governorate.cities')}}', {
                            params: { governorate_id: governorate_id }
                        });
                    }).then(function (response) {
                        if (!response) return;
                        $('#city_id').children('option').remove();
                        $('#city_id').select2({
                            placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.city')}}',
                            data: response.data,
                        }).val(city_id).change();
                    });
                    "
                 x-data="{country_code: @entangle('country_code').defer, countries: {{json_encode($countries)}},}"
                 x-init="$nextTick(() => {
                 select2 = $('#country_code').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.country')}}',
                        cache: true
                    }).val(country_code).change();
                    select2.on('select2:select', (event) => {
                        country_code = event.target.value;
                        window.livewire.emit('setCountry', country_code);
                        $dispatch('governorates', {country_code: (country_code !== 'none') ? country_code : null});
                        $dispatch('select-governorate', {country_code: (country_code !== 'none') ? country_code : null});
                    });
                })">
                <label for="country_code">{{__('pages/advertisers/index.modal.edit.inputs.country')}}</label>
                <select x-model="country_code" x-cloak
                        data-placeholder="{{__('pages/advertisers/index.modal.edit.inputs.placeholders.country')}}"
                        id="country_code"
                        x-ref="country_code"
                        x-bind:value="country_code"
                        class="form-control @error('user.country_code') is-invalid @enderror"
                        wire:model.defer="country_code">
                    <option></option>
                    @foreach ($countries as $country)
                        <option @if($country['id'] == $country_code) selected
                                @endif  value="{{$country['id']}}">{{$country['value']}}</option>
                    @endforeach
                </select>
                @error('user.country_code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div x-subscribe="country_code" wire:ignore
                 x-data="{add: false, country_code: null}"
                 x-on:governorates.window="country_code = $event.detail.country_code"
                 x-init="country_code = null">
                <template x-if="country_code">
                    <div class="form-group" x-data="{governorate_id: @entangle('user.governorate_id').defer}"
                         x-init="$nextTick(() => {
                        $('#governorate_id').select2().on('select2:select', (event) => {
                            governorate_id = $('#governorate_id').val();
                            window.livewire.emit('setGovernorate', governorate_id);
                            $dispatch('cities', {governorate_id: governorate_id});
                            $dispatch('select-city', {governorate_id: governorate_id});
                        });
                        if (governorate_id && governorate_id !== 'none') {
                            $dispatch('cities', {governorate_id: governorate_id});
                        }
                    })">
                        <label for="governorate_id">{{__('pages/advertisers/index.modal.edit.inputs.governorate')}}</label>
                        <select x-cloak x-model="governorate_id" name="governorate_id"
                                data-placeholder="{{__('pages/advertisers/index.modal.edit.inputs.placeholders.governorate')}}"
                                id="governorate_id"
                                class="form-control select2 @error('user.governorate_id') is-invalid @enderror"
                                x-ref="governorate_id"
                                x-bind:value="governorate_id">
                            <option></option>
                        </select>
                        @error('user.governorate_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </template>
            </div>
            <div x-subscribe="governorate_id" wire:ignore
                 x-data="{add: false, governorate_id: null}"
                 x-on:cities.window="governorate_id = $event.detail.governorate_id"
                 x-init="governorate_id = null">
                <template x-if="governorate_id">
                    <div class="form-group" x-data="{city_id: @entangle('user.city_id').defer}"
                         x-init="$nextTick(() => {
                        $('#city_id').select2().on('select2:select', (event) => {
                            city_id = $('#city_id').val();
                        })
                    })">
                        <label for="city_id">{{__('pages/advertisers/index.modal.edit.inputs.city')}}</label>
                        <select x-cloak x-model="city_id" name="city_id"
                                data-placeholder="{{__('pages/advertisers/index.modal.edit.inputs.placeholders.city')}}"
                                id="city_id"
                                class="form-control select2 @error('user.city_id') is-invalid @enderror"
                                x-ref="city_id"
                                x-bind:value="city_id">
                            <option></option>
                        </select>
                        @error('user.city_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </template>
            </div>
            <div class="form-group">
                <label for="language">{{__('pages/advertisers/index.modal.edit.inputs.language')}}</label>
                <select class="form-control @error('user.language_code') is-invalid @enderror"
                        wire:model.defer="user.language_code"
                        id="language">
                    <option
                        value="none">{{__('pages/advertisers/index.modal.edit.inputs.placeholders.language')}}</option>
                    @foreach($languages as $index => $language)
                        <option value="{{$index}}">{{$language}}</option>
                    @endforeach
                </select>
                @error('user.language_code')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="rate">{{__('pages/advertisers/index.modal.edit.inputs.rate')}}</label>
                <input class="form-control @error('user.rate') is-invalid @enderror" id="rate"
                       name="rate" type="number" min="0" max="5" step="0.1"
                       wire:model.defer="user.rate">
                @error('user.rate')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="contact_number">{{__('pages/advertisers/index.modal.edit.inputs.contact_number')}}</label>
                <input dir="ltr" class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('user.contact_number') is-invalid @enderror" id="contact_number"
                       name="contact_number"
                       wire:model.defer="user.contact_number">
                @error('user.contact_number')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="whatsapp_number">{{__('pages/advertisers/index.modal.edit.inputs.whatsapp_number')}}</label>
                <input dir="ltr" class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('user.whatsapp_number') is-invalid @enderror" id="whatsapp_number"
                       name="whatsapp_number"
                       wire:model.defer="user.whatsapp_number">
                @error('user.whatsapp_number')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="facebook_url">{{__('pages/advertisers/index.modal.edit.inputs.facebook_url')}}</label>
                <input class="form-control @error('user.facebook_url') is-invalid @enderror" id="facebook_url"
                       name="facebook_url"
                       wire:model.defer="user.facebook_url">
                @error('user.facebook_url')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="twitter_url">{{__('pages/advertisers/index.modal.edit.inputs.twitter_url')}}</label>
                <input class="form-control @error('user.twitter_url') is-invalid @enderror" id="twitter_url"
                       name="twitter_url"
                       wire:model.defer="user.twitter_url">
                @error('user.twitter_url')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="website_url">{{__('pages/advertisers/index.modal.edit.inputs.website_url')}}</label>
                <input class="form-control @error('user.website_url') is-invalid @enderror" id="website_url"
                       name="website_url"
                       wire:model.defer="user.website_url">
                @error('user.website_url')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label
                    for="allowed_posts_count">{{__('pages/advertisers/index.modal.edit.inputs.allowed_posts_count')}}</label>
                <input type="number" class="form-control @error('user.allowed_posts_count') is-invalid @enderror"
                       id="allowed_posts_count"
                       name="allowed_posts_count"
                       wire:model.defer="user.allowed_posts_count">
                @error('user.allowed_posts_count')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label
                    for="allowed_offers_count">{{__('pages/advertisers/index.modal.edit.inputs.allowed_offers_count')}}</label>
                <input type="number" class="form-control @error('user.allowed_offers_count') is-invalid @enderror"
                       id="allowed_offers_count"
                       name="allowed_offers_count"
                       wire:model.defer="user.allowed_offers_count">
                @error('user.allowed_offers_count')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label
                    for="maximum_monthly_offers">{{__('pages/advertisers/index.modal.edit.inputs.maximum_monthly_offers')}}</label>
                <input type="number" class="form-control @error('user.maximum_monthly_offers') is-invalid @enderror"
                       id="maximum_monthly_offers"
                       name="maximum_monthly_offers"
                       wire:model.defer="user.maximum_monthly_offers">
                @error('user.maximum_monthly_offers')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="status">{{__('pages/advertisers/index.modal.edit.inputs.status')}}</label>
                <x-select wire:model.defer="user.status"
                          :options="['active' => __('pages/advertisers/index.modal.edit.inputs.status_options.active'), 'inactive' => __('pages/advertisers/index.modal.edit.inputs.status_options.inactive'), 'banned' => __('pages/advertisers/index.modal.edit.inputs.status_options.banned')]"
                          id="status"></x-select>
                @error('user.status')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="is_elite">{{__('pages/advertisers/index.modal.edit.inputs.is_elite')}}</label>
                <x-select wire:model.defer="user.is_elite"
                          :options="[1 => __('pages/advertisers/index.modal.edit.inputs.boolean.yes'), 0 => __('pages/advertisers/index.modal.edit.inputs.boolean.no')]"
                          id="is_elite"></x-select>

                @error('user.is_elite')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label
                    for="accepted_send_notification">{{__('pages/advertisers/index.modal.edit.inputs.accepted_send_notification')}}</label>
                <x-select wire:model.defer="user.is_accepted_send_notifications"
                          :options="[1 => __('pages/advertisers/index.modal.edit.inputs.boolean.yes'), 0 => __('pages/advertisers/index.modal.edit.inputs.boolean.no')]"
                          id="accepted_send_notification"></x-select>
                @error('user.is_accepted_send_notifications')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="email_verified">{{__('pages/advertisers/index.modal.edit.inputs.email_verified')}}</label>
                <select class="form-control" id="email_verified" wire:model.defer="user.email_verified_at">
                    <option value="1">{{__('pages/advertisers/index.modal.edit.inputs.boolean.yes')}}</option>
                    <option value="0">{{__('pages/advertisers/index.modal.edit.inputs.boolean.no')}}</option>
                </select>
                @error('user.email_verified_at')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="mobile_verified">{{__('pages/advertisers/index.modal.edit.inputs.mobile_verified')}}</label>
                <select class="form-control" id="mobile_verified" wire:model.defer="user.mobile_verified_at">
                    <option value="1">{{__('pages/advertisers/index.modal.edit.inputs.boolean.yes')}}</option>
                    <option value="0">{{__('pages/advertisers/index.modal.edit.inputs.boolean.no')}}</option>
                </select>
                @error('user.mobile_verified_at')
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
    </form>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->


@push('scripts')
    <script type="text/javascript">
        document.addEventListener('livewire:load', function () {
            $('#business_type').select2({
                placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.business_type')}}',
            });
        })

        //start function on change.
        $('#business_type').on('change', function (e) {
            let item = $('#business_type').select2("val");
            window.livewire.emit('setBusinessType', item);
        });

        //add event listener to refresh select2 function
        window.addEventListener('refreshSelect2Create', () => {
            $('#business_type').select2({
                placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.business_type')}}',
            });
        });

        $("#country").change(function () {
            let selectedCountry = $(this).children("option:selected").val();
            window.livewire.emit('setCountry', selectedCountry);
        });

        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#new_image').val(null);
        });

        window.addEventListener('select-governorate', (el) => {
            axios.get('{{route('admin.country.governorates')}}', {
                params: {
                    country_code: el.detail.country_code,
                }
            }).then(function (response) {
                $('#governorate_id').children('option').remove();
                $('#governorate_id').select2({
                    placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.governorate')}}',
                    data: response.data,
                }).val('').change();
                $('#city_id').children('option').remove();
                $('#city_id').select2().val('').change();
            })
        });

        window.addEventListener('select-city', (el) => {
            const fillCities = () => {
                if (!$('#city_id').length) {
                    setTimeout(fillCities, 50);
                    return;
                }
                axios.get('{{route('admin.governorate.cities')}}', {
                    params: {
                        governorate_id: el.detail.governorate_id,
                    }
                }).then(function (response) {
                    $('#city_id').children('option').remove();
                    $('#city_id').select2({
                        placeholder: '{{__('pages/advertisers/index.modal.edit.inputs.placeholders.city')}}',
                        data: response.data,
                    }).val('').change();
                });
            };
            fillCities();
        });
    </script>
@endpush
