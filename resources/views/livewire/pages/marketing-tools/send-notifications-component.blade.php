<div>
    <form wire:submit.prevent="sendNotification">
        {{-- Targeting. Each select2 sits in its own wire:ignore block (select2 rewrites the DOM
             Livewire would otherwise morph back) and pushes its value to Livewire on change, so
             the server always knows the exact selection and the preview below stays accurate. --}}
        <div class="form-group row">
            <label class="col-form-label col-lg-2" for="recipients_type">{{__('pages/marketing-tools/notifications.content.recipients')}}</label>
            <div class="col-lg-10">
                <div wire:ignore>
                    <select id="recipients_type" class="form-control js-notify-select2" data-model="recipients_type">
                        @foreach(\App\Http\Livewire\MarketingTools\SendNotificationsComponent::RECIPIENT_TYPES as $type)
                            <option value="{{$type}}" @if($recipients_type === $type) selected @endif>{{__('pages/marketing-tools/notifications.content.' . str_replace('_', '-', $type))}}</option>
                        @endforeach
                    </select>
                </div>
                @error('recipients_type')
                <div class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></div>
                @enderror
            </div>
        </div>

        @if($recipients_type === 'specific_advertisers' || $recipients_type === 'specific_customers')
            @php($userList = $recipients_type === 'specific_advertisers' ? $all_advertisers : $all_customers)
            <div class="form-group row">
                <label class="col-form-label col-lg-2" for="notify_recipients">
                    {{__('pages/marketing-tools/notifications.content.' . ($recipients_type === 'specific_advertisers' ? 'advertisers' : 'customers'))}}
                </label>
                <div class="col-lg-10">
                    <div wire:ignore wire:key="recipients-{{$recipients_type}}">
                        <select id="notify_recipients" multiple class="form-control js-notify-select2" data-model="recipients"
                                data-placeholder="{{__('pages/marketing-tools/notifications.content.search_users')}}">
                            @foreach($userList as $user)
                                <option value="{{$user['id']}}">{{$user['name']}}{{ $user['push'] ? '' : ' — ' . __('pages/marketing-tools/notifications.content.no_push') }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('recipients')
                    <div class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>
            </div>
        @endif

        @if($recipients_type === 'filtered')
            {{-- Who: customers and/or advertisers, optionally narrowed to some business types --}}
            <div class="form-group row">
                <label class="col-form-label col-lg-2">{{__('pages/marketing-tools/notifications.content.groups')}}</label>
                <div class="col-lg-10 pt-2">
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="include_customers" wire:model="include_customers">
                        <label class="custom-control-label" for="include_customers">{{__('pages/marketing-tools/notifications.content.group_customers')}}</label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="include_advertisers" wire:model="include_advertisers">
                        <label class="custom-control-label" for="include_advertisers">{{__('pages/marketing-tools/notifications.content.group_advertisers')}}</label>
                    </div>
                </div>
            </div>

            @if($include_advertisers)
                <div class="form-group row">
                    <label class="col-form-label col-lg-2" for="notify_business_types">{{__('pages/marketing-tools/notifications.content.business_types')}}</label>
                    <div class="col-lg-10">
                        <div wire:ignore wire:key="notify-business-types">
                            <select id="notify_business_types" multiple class="form-control js-notify-select2" data-model="business_types"
                                    data-placeholder="{{__('pages/marketing-tools/notifications.content.all_business_types')}}">
                                @foreach($all_business_types as $id => $name)
                                    <option value="{{$id}}">{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('business_types.*')
                        <div class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="form-group row">
                <label class="col-form-label col-lg-2" for="notify_categories">{{__('pages/marketing-tools/notifications.content.categories')}}</label>
                <div class="col-lg-10" wire:ignore wire:key="notify-categories">
                    <select id="notify_categories" multiple class="form-control js-notify-select2" data-model="categories">
                        @foreach($all_categories as $category)
                            <optgroup label="{{$category['name']}}">
                                <option value="{{$category['id']}}">{{$category['name']}}</option>
                                @foreach($category['children'] as $child)
                                    <option value="{{$child['id']}}">{{$child['name']}}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-lg-2" for="notify_governorates">{{__('pages/marketing-tools/notifications.content.governorates')}}</label>
                <div class="col-lg-10" wire:ignore wire:key="notify-governorates">
                    <select id="notify_governorates" multiple class="form-control js-notify-select2" data-model="governorates">
                        @foreach($all_governorates as $governorate)
                            <option value="{{$governorate['id']}}">{{$governorate['name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-lg-2" for="notify_cities">{{__('pages/marketing-tools/notifications.content.cities')}}</label>
                <div class="col-lg-10" wire:ignore wire:key="notify-cities">
                    <select id="notify_cities" multiple class="form-control js-notify-select2" data-model="cities">
                        @foreach($all_governorates as $governorate)
                            <optgroup label="{{$governorate['name']}}">
                                @foreach($governorate['cities'] as $city)
                                    <option value="{{$city['id']}}">{{$city['name']}}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Activity --}}
            <div class="form-group row">
                <label class="col-form-label col-lg-2">{{__('pages/marketing-tools/notifications.content.push')}}</label>
                <div class="col-lg-10 pt-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="push_only" wire:model="push_only">
                        <label class="custom-control-label" for="push_only">{{__('pages/marketing-tools/notifications.content.push_only')}}</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-lg-2">{{__('pages/marketing-tools/notifications.content.registered')}}</label>
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <div class="input-group">
                                <span class="input-group-prepend"><span class="input-group-text">{{__('pages/marketing-tools/notifications.content.from')}}</span></span>
                                <input type="date" class="form-control @error('registered_from') is-invalid @enderror" wire:model="registered_from">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-prepend"><span class="input-group-text">{{__('pages/marketing-tools/notifications.content.to')}}</span></span>
                                <input type="date" class="form-control @error('registered_to') is-invalid @enderror" wire:model="registered_to">
                            </div>
                        </div>
                    </div>
                    @error('registered_to')
                    <div class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-lg-2" for="login_filter">{{__('pages/marketing-tools/notifications.content.last_login')}}</label>
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <select id="login_filter" class="form-control" wire:model="login_filter">
                                <option value="">{{__('pages/marketing-tools/notifications.content.login_any')}}</option>
                                @foreach(\App\Http\Livewire\MarketingTools\SendNotificationsComponent::LOGIN_FILTERS as $option)
                                    <option value="{{$option}}">{{__('pages/marketing-tools/notifications.content.login_' . $option)}}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(in_array($login_filter, ['active', 'inactive'], true))
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="number" min="1" class="form-control @error('login_days') is-invalid @enderror" wire:model.debounce.500ms="login_days">
                                    <span class="input-group-append"><span class="input-group-text">{{__('pages/marketing-tools/notifications.content.days')}}</span></span>
                                </div>
                            </div>
                        @endif
                    </div>
                    @error('login_days')
                    <div class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-lg-10 offset-lg-2">
                    <small class="text-muted">{{__('pages/marketing-tools/notifications.content.filtered_hint')}}</small>
                </div>
            </div>
        @endif

        <div class="form-group row">
            <div class="col-lg-10 offset-lg-2">
                @if($preview['needs_selection'])
                    <div class="alert alert-warning mb-0">{{__('pages/marketing-tools/notifications.content.preview_needs_selection')}}</div>
                @else
                    <div class="alert {{ $preview['total'] > 0 ? 'alert-info' : 'alert-danger' }} mb-0">
                        <div class="font-weight-bold mb-1">
                            <i class="icon-users mr-1"></i>
                            {{__('pages/marketing-tools/notifications.content.preview_total', ['total' => $preview['total']])}}
                            <span wire:loading class="spinner-border spinner-border-sm ml-1"></span>
                        </div>
                        <div>
                            {{__('pages/marketing-tools/notifications.content.preview_customers', ['count' => $preview['customers']])}}
                            ·
                            {{__('pages/marketing-tools/notifications.content.preview_advertisers', ['count' => $preview['advertisers']])}}
                            @if($preview['by_business_type']->isNotEmpty())
                                ({{ $preview['by_business_type']->map(fn ($count, $name) => $name . ' ' . $count)->implode('، ') }})
                            @endif
                        </div>
                        <div>{{__('pages/marketing-tools/notifications.content.preview_push', ['push' => $preview['push']])}}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="subject">{{__('pages/marketing-tools/notifications.content.subject')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('subject') is-invalid @enderror" type="text" id="subject"
                       wire:model.defer="subject"/>
                @error('subject')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="subject_en">{{__('pages/marketing-tools/notifications.content.subject_en')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('subject_en') is-invalid @enderror" type="text" id="subject_en"
                       wire:model.defer="subject_en"/>
                @error('subject_en')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        {{--
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="image">{{__('pages/marketing-tools/notifications.content.image')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('image') is-invalid @enderror" type="text" id="image"
                       wire:model.defer="image"/>
                @error('image')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        --}}
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="body">{{__('pages/marketing-tools/notifications.content.body')}}</label>
            <div class="col-lg-10">
                <textarea id="body" wire:model.defer="body"
                          class="form-control h-100 @error('body') is-invalid @enderror"></textarea>
                @error('body')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="body_en">{{__('pages/marketing-tools/notifications.content.body_en')}}</label>
            <div class="col-lg-10">
                <textarea id="body_en" wire:model.defer="body_en"
                          class="form-control h-100 @error('body_en') is-invalid @enderror"></textarea>
                @error('body_en')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>


        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="notify_link">{{__('pages/marketing-tools/notifications.notify_link')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('notify_link') is-invalid @enderror" type="text" id="notify_link"
                       wire:model.defer="notify_link"/>
                @error('notify_link')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>

        <div class="text-right">
            <button type="submit" wire:loading.remove wire:key="submit" class="btn btn-teal"
                    @if($preview['needs_selection'] || $preview['total'] === 0) disabled @endif>{{__('pages/marketing-tools/notifications.content.title')}}
                <i class="icon-paperplane ml-2"></i>
            </button>
            <i wire:key="submitting" wire:loading.class="spinner-border text-dark mr-2"></i>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {
            const component = @this;
            const init = function () {
                $('.js-notify-select2').each(function () {
                    const $el = $(this);
                    if ($el.data('select2')) return;
                    $el.select2({width: '100%', placeholder: $el.data('placeholder') || '', allowClear: $el.prop('multiple')});
                    $el.on('change', function () {
                        component.set($el.data('model'), $el.val());
                    });
                });
            };
            init();
            Livewire.hook('message.processed', init);

            window.addEventListener('clear-select', function () {
                $('.js-notify-select2').each(function () {
                    const $el = $(this);
                    $el.val($el.prop('multiple') ? [] : 'filtered').trigger('change.select2');
                });
            });
        });
    </script>
</div>
