<div>
    <form wire:submit.prevent="sendEmail" type="multipart">
        <div>
            <div wire:ignore class="form-group row" x-data="{recipients_type: @entangle('recipients_type').defer}"
                 x-on:reset-recipients.window="recipients_type = $event.detail;$($refs.select).select2().val($event.detail).change();"
                 x-init="$nextTick(() => {let select2 = $($refs.select).select2().val(recipients_type).change();select2.on('change', (event) => {recipients_type = event.target.value; $dispatch('new-recipient-type', recipients_type)});})">
                <label class="col-form-label col-lg-2"
                       for="recipients_type">{{__('pages/marketing-tools/emails.content.recipients')}}</label>
                <div class="col-lg-10">
                    <select x-model="recipients_type"
                            id="recipients_type"
                            class="form-control select2 @error('recipients_type') is-invalid @enderror"
                            x-ref="select"
                            x-bind:value="recipients_type">
                        <option
                            value="all_users">{{__('pages/marketing-tools/emails.content.all-users')}}</option>
                        <option
                            value="all_advertisers">{{__('pages/marketing-tools/emails.content.all-advertisers')}}</option>
                        <option
                            value="all_customers">{{__('pages/marketing-tools/emails.content.all-customers')}}</option>
                        <option
                            value="specific_advertisers">{{__('pages/marketing-tools/emails.content.specific-advertisers')}}</option>
                        <option
                            value="specific_customers">{{__('pages/marketing-tools/emails.content.specific-customers')}}</option>
                        <option
                            value="specific_emails">{{__('pages/marketing-tools/emails.content.specific-emails')}}</option>
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
                    <div wire:ignore class="form-group row" x-data="{advertisers: @entangle('advertisers').defer}"
                         x-init="$nextTick(() => {let select2 = $($refs.select).select2({ multiple: true}).val('').change();select2.on('change', (event) => {advertisers = $('#advertisers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="advertisers">{{__('pages/marketing-tools/emails.content.advertisers')}}</label>
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
                         x-init="$nextTick(() => {let select2 = $($refs.select).select2({ multiple: true}).val('').change();select2.on('change', (event) => {customers = $('#customers').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="customers">{{__('pages/marketing-tools/emails.content.customers')}}</label>
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
                <template x-if="recipient === 'specific_emails'">
                    <div wire:ignore class="form-group row" x-data="{emails: @entangle('emails').defer}"
                         x-init="$nextTick(() => {let select2 = $($refs.select).select2({multiple: true, tags: true}).val('').change();select2.on('change', (event) => {emails = $('#emails').val(); });})">
                        <label class="col-form-label col-lg-2"
                               for="emails">{{__('pages/marketing-tools/emails.content.emails')}}</label>
                        <div class="col-lg-10">
                            <select x-cloak multiple x-model="emails" name="emails[]"
                                    id="emails" class="form-control select2 @error('emails') is-invalid @enderror"
                                    x-ref="select"
                                    x-bind:value="emails">
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
                   for="subject">{{__('pages/marketing-tools/emails.content.subject')}}</label>
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
        <div wire:ignore class="form-group row" x-data="{body: @entangle('body').defer}"
             x-init="$nextTick(() => {
                    $('#body').summernote({
                        height: 300
                    });

                    $('.summernote').on('summernote.change', function (we, contents, $editable) {
                        body = contents;
                    });
                })">
            <label class="col-form-label col-lg-2"
                   for="body">{{__('pages/marketing-tools/emails.content.body')}}</label>
            <div class="col-lg-10">
                <textarea x-cloak x-model="body" id="body" x-ref="textarea"
                          x-bind:value="body"
                          class="form-control summernote summernote-borderless @error('body') is-invalid @enderror"
                          rows="4" cols="4">
                </textarea>
                @error('body')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="attachments">{{__('pages/marketing-tools/emails.content.attachments')}}</label>
            <div class="col-lg-10">
                <input class="form-control h-100 @error('attachments') is-invalid @enderror" type="file"
                       multiple="multiple"
                       id="attachments"
                       wire:model.defer="attachments"/>
                @error('attachments.*')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="text-right">
            <button type="submit" wire:loading.remove wire:key="submit" class="btn btn-teal">{{__('pages/marketing-tools/emails.content.title')}}
                <i class="icon-paperplane ml-2"></i>
                <i wire:key="submitting" wire:loading.class="spinner-border text-dark mr-2"></i>
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
            // $('#recipients_type').select2().val("all_users").change();


            $('#body').summernote('code', '');
            $('#attachments').val("");
        })
    </script>
@endpush
