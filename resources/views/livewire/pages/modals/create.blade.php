<div class="card-body">
    <form wire:submit.prevent="store">
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
                    </select>
                    @error('recipients_type')
                    <div class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </div>
                    @enderror
                </div>
            </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="title_en">{{__('pages/modals/index.modal.edit.inputs.title_en')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('title_en') is-invalid @enderror" id="title_en"
                       name="title_en" wire:model.defer="title_en"
                       />
                @error('title_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="title_ar">{{__('pages/modals/index.modal.edit.inputs.title_ar')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('title_ar') is-invalid @enderror" id="title_ar"
                       name="title_ar" wire:model.defer="title_ar"
                       />
                @error('title_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="link">{{__('pages/modals/index.modal.edit.inputs.link')}}</label>
            <div class="col-lg-10">
                <input type="url" class="form-control @error('link') is-invalid @enderror" id="link"
                       name="link" wire:model.defer="link"
                       />
                @error('link')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="body_en">{{__('pages/modals/index.modal.edit.inputs.body_en')}}</label>
            <div class="col-lg-10">
                <textarea type="text" class="form-control @error('body_en') is-invalid @enderror"
                          id="body_en"
                          wire:model.defer="body_en"
                          ></textarea>
                @error('body_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="body_ar">{{__('pages/modals/index.modal.edit.inputs.body_ar')}}</label>
            <div class="col-lg-10">
                <textarea type="text" class="form-control @error('body_ar') is-invalid @enderror"
                          id="body_ar"
                          wire:model.defer="body_ar"
                          ></textarea>
                @error('body_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-lg-2"
             for="start_at">{{__('pages/modals/index.modal.edit.inputs.start_at')}}</label>
            <div class="col-lg-10">

             <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" id="start_at"
                   wire:model.defer="start_at"/>
            </div>

            @error('start_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="form-group row">
            <label class="col-form-label col-lg-2"
             for="end_at">{{__('pages/modals/index.modal.edit.inputs.end_at')}}</label>
            <div class="col-lg-10">

             <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror" id="end_at"
                   wire:model.defer="end_at"/>
            </div>
                   @error('end_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/modals/index.modal.edit.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>


@push('scripts')
    <script type="text/javascript">
        /*document.addEventListener('livewire:load', function (){
            $('#parent').select2({
                placeholder: '{{__('pages/modals/inquiry.modal.edit.inputs.placeholders.modals')}}',
                allowClear: true,

            });
        })*/
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#image').val(null);
        });

       /* //add event listener to refresh select2 function
        window.addEventListener('refreshSelect2Create', () => {
            $('#parent').select2({
                placeholder: '{{__('pages/modals/inquiry.modal.edit.inputs.placeholders.modals')}}',
                allowClear: true,

            });
        });
        //start function on change.
        $('#parent').on('change', function (e) {
            let item = $('#parent').select2("val");
            window.livewire.emit('setParentCategoryCreate', item);
        });*/
    </script>
@endpush
