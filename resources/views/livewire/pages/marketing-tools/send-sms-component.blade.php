<div>
    <form wire:submit.prevent="sendSMS" type="multipart">
        <div>
            <div wire:ignore class="form-group row" x-data="{recipients_type: @entangle('recipients_type').defer}"
                 x-on:reset-recipients.window="recipients_type = $event.detail;$($refs.select).select2().val($event.detail).change();"
                 x-init="$nextTick(() => {select2 = $($refs.select).select2().val(recipients_type).change();select2.on('change', (event) => {recipients_type = event.target.value; $dispatch('new-recipient-type', recipients_type)});})">
                <label class="col-form-label col-lg-2"
                       for="recipients_type">{{__('pages/marketing-tools/sms.content.recipients')}}</label>
                <div class="col-lg-10">
                    <select x-model="recipients_type"
                            id="recipients_type"
                            class="form-control select2 @error('recipients_type') is-invalid @enderror"
                            x-ref="select"
                            x-bind:value="recipients_type">
                        <option
                            value="all_users">{{__('pages/marketing-tools/sms.content.all-users')}}</option>
                        <option
                            value="all_advertisers">{{__('pages/marketing-tools/sms.content.all-advertisers')}}</option>
                        <option
                            value="all_customers">{{__('pages/marketing-tools/sms.content.all-customers')}}</option>
                        <option
                            value="specific_advertisers">{{__('pages/marketing-tools/sms.content.specific-advertisers')}}</option>
                        <option
                            value="specific_customers">{{__('pages/marketing-tools/sms.content.specific-customers')}}</option>
                        <option
                            value="specific_numbers">{{__('pages/marketing-tools/sms.content.specific-numbers')}}</option>
                    </select>
                    @error('recipients_type')
                    <div class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </div>
                    @enderror
                </div>
            </div>
            <div x-subscribe="recipient" x-data="{ recipient: 'all_users' }"
                 x-on:new-recipient-type.window="recipient = $event.detail;">
                <template x-if="recipient === 'specific_advertisers'">
                    <div wire:ignore class="form-group row" x-data="{advertisers: @entangle('advertisers').defer}"
                         x-init="$nextTick(() => {select2 = $($refs.select).select2({ multiple: true}).val('').change();select2.on('change', (event) => {advertisers = $('#advertisers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="advertisers">{{__('pages/marketing-tools/sms.content.advertisers')}}</label>
                        <div class="col-lg-10">
                            <select x-cloak multiple x-model="advertisers" name="advertisers[]"
                                    id="advertisers"
                                    class="form-control select2 @error('advertisers') is-invalid @enderror"
                                    x-ref="select"
                                    x-bind:value="advertisers">
                                @foreach($all_advertisers as $advertiser)
                                    <option
                                        value="{{$advertiser['id']}}">{{$advertiser['name']}}</option>
                                @endforeach
                            </select>
                            @error('advertisers')
                            <div class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>
                </template>
                <template x-if="recipient === 'specific_customers'">
                    <div wire:ignore class="form-group row" x-data="{customers: @entangle('customers').defer}"
                         x-init="$nextTick(() => {select2 = $($refs.select).select2({ multiple: true}).val('').change();select2.on('change', (event) => {customers = $('#customers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="customers">{{__('pages/marketing-tools/sms.content.customers')}}</label>
                        <div class="col-lg-10">
                            <select x-cloak multiple x-model="customers" name="customers[]"
                                    id="customers"
                                    class="form-control select2 @error('customers') is-invalid @enderror"
                                    x-ref="select"
                                    x-bind:value="customers">
                                @foreach($all_customers as $customer)
                                    <option
                                        value="{{$customer['id']}}">{{$customer['name']}}</option>
                                @endforeach
                            </select>
                            @error('customers')
                            <div class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>
                </template>
                <template x-if="recipient === 'specific_numbers'">
                    <div wire:ignore class="form-group row" x-data="{numbers: @entangle('numbers').defer}"
                         x-init="$nextTick(() => {let select2 = $($refs.select).select2({multiple: true, tags: true,}).val('').change();select2.on('change', (event) => {numbers = $('#numbers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="numbers">{{__('pages/marketing-tools/sms.content.numbers')}}</label>
                        <div class="col-lg-10">
                            <select x-cloak multiple x-model="numbers" name="numbers[]"
                                    id="numbers" class="form-control select2 @error('numbers') is-invalid @enderror"
                                    x-ref="select"
                                    x-bind:value="numbers">
                                <option value="none"></option>
                            </select>
                            @error('customers')
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
                   for="subject">{{__('pages/marketing-tools/sms.content.subject')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('subject') is-invalid @enderror" type="text" id="subject"
                       wire:model.defer="subject" required/>
                @error('subject')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="body">{{__('pages/marketing-tools/sms.content.body')}}</label>
            <div class="col-lg-10">
                <textarea id="body"
                          class="form-control @error('body') is-invalid @enderror"
                          wire:model.defer="body" maxlength="130" required>
                </textarea>
                @error('body')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-teal">{{__('pages/marketing-tools/sms.content.title')}}
                <i class="icon-paperplane ml-2"></i>
            </button>
        </div>
    </form>
</div>
@push('scripts')
    <script type="text/javascript">
        document.addEventListener('clearData', function () {
            let selects = document.querySelectorAll('select.select2:not(#recipients_type)');

            selects.forEach((x) => {
                $(x).select2().val("").change();
            })
        })
    </script>
@endpush
