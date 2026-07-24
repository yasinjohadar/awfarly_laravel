<div>
    <form wire:submit.prevent="sendNotification">
        <div>
            <div class="form-group row" wire:ignore x-data="{recipients_type: @entangle('recipients_type').defer}"
                 x-on:clear-select.window="{
                    recipients_type = 'all_users';
                    $($refs.recipients_type).select2().val(recipients_type).change();
                    /*$($refs.recipient).select2().val('').change();*/
                 }"
                 x-init="$nextTick(() => {select2 = $($refs.recipients_type).select2().val(recipients_type).change();select2.on('change', (event) => {recipients_type = event.target.value; $dispatch('new-recipient-type', recipients_type)});})">
                <label class="col-form-label col-lg-2"
                       for="recipients_type">{{__('pages/marketing-tools/notifications.content.recipients')}}</label>
                <div class="col-lg-10">
                    <select x-model="recipients_type"
                            id="recipients_type"
                            class="form-control select2 @error('recipients_type') is-invalid @enderror"
                            x-ref="recipients_type"
                            x-bind:value="recipients_type">
                        <option
                            value="all_users">{{__('pages/marketing-tools/notifications.content.all-users')}}</option>
                        <option
                            value="all_advertisers">{{__('pages/marketing-tools/notifications.content.all-advertisers')}}</option>
                        <option
                            value="all_customers">{{__('pages/marketing-tools/notifications.content.all-customers')}}</option>
                        <option
                            value="specific_advertisers">{{__('pages/marketing-tools/notifications.content.specific-advertisers')}}</option>
                        <option
                            value="specific_customers">{{__('pages/marketing-tools/notifications.content.specific-customers')}}</option>
                    </select>
                    @error('recipients_type')
                    <div class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </div>
                    @enderror
                </div>
            </div>
            <div wire:ignore x-subscribe="recipient" x-data="{ recipient: 'all_users' }"
                 x-on:new-recipient-type.window="recipient = $event.detail;">
                <template x-if="recipient === 'specific_advertisers'">
                    <div wire:ignore class="form-group row" x-data="{recipients: @entangle('recipients').defer}"
                         x-init="$nextTick(() => {select2 = $($refs.recipient).select2().val('').change();select2.on('change', (event) => {recipients = $('#advertisers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="advertisers">{{__('pages/marketing-tools/notifications.content.advertisers')}}</label>
                        <div class="col-lg-10">
                            <select x-cloak multiple x-model="recipients" name="recipients[]"
                                    id="advertisers"
                                    class="form-control select2 @error('recipients') is-invalid @enderror"
                                    x-ref="recipient"
                                    x-bind:value="recipients">
                                @foreach($all_advertisers as $advertiser)
                                    <option
                                        value="{{$advertiser['id']}}">{{$advertiser['name']}}</option>
                                @endforeach
                            </select>
                            @error('recipients')
                            <div class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>
                </template>
                <template x-if="recipient === 'specific_customers'">
                    <div wire:ignore class="form-group row" x-data="{recipients: @entangle('recipients').defer}"
                         x-init="$nextTick(() => {select2 = $($refs.recipient).select2({ multiple: true}).val('').change();select2.on('change', (event) => {recipients = $('#customers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="customers">{{__('pages/marketing-tools/notifications.content.customers')}}</label>
                        <div class="col-lg-10">
                            <select x-cloak multiple x-model="recipients" name="recipients[]"
                                    id="customers"
                                    class="form-control select2 @error('recipients') is-invalid @enderror"
                                    x-ref="recipient"
                                    x-bind:value="recipients">
                                @foreach($all_customers as $customer)
                                    <option
                                        value="{{$customer['id']}}">{{$customer['name']}}</option>
                                @endforeach
                            </select>
                            @error('recipients')
                            <div class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>
                </template>
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
            <button type="submit" wire:loading.remove wire:key="submit" class="btn btn-teal">{{__('pages/marketing-tools/notifications.content.title')}}
                <i class="icon-paperplane ml-2"></i>
            </button>
            <i wire:key="submitting" wire:loading.class="spinner-border text-dark mr-2"></i>
        </div>
    </form>
</div>
