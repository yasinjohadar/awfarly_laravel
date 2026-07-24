<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$modal['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group" wire:ignore >
        <label class="" for="recipients_type">{{__('pages/marketing-tools/notifications.content.recipients')}}</label>
            <select wire:model="modal.recipients_type"
                    id="recipients_type"
                    class="form-control select2 @error('modal.recipients_type') is-invalid @enderror">
                <option
                    value="all_users">{{__('pages/marketing-tools/notifications.content.all-users')}}</option>
                <option
                    value="all_advertisers">{{__('pages/marketing-tools/notifications.content.all-advertisers')}}</option>
                <option
                    value="all_customers">{{__('pages/marketing-tools/notifications.content.all-customers')}}</option>
            </select>
            @error('modal.recipients_type')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group">
            <label for="title_en">{{__('pages/modals/index.modal.edit.inputs.title_en')}}</label>
            <input type="text" class="form-control @error('modal.title_en') is-invalid @enderror" id="title_en"
                   name="name"
                   wire:model.defer="modal.title_en"
            >
            @error('modal.title_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="title_ar">{{__('pages/modals/index.modal.edit.inputs.title_ar')}}</label>
            <input type="text" class="form-control @error('modal.title_ar') is-invalid @enderror" id="title_ar"
                   name="name"
                   wire:model.defer="modal.title_ar"
            >
            @error('modal.title_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="body_en">{{__('pages/modals/index.modal.edit.inputs.body_en')}}</label>
            <textarea type="text" class="form-control @error('modal.body_en') is-invalid @enderror"
                      id="body_en"
                      wire:model.defer="modal.body_en"
                      ></textarea>
            @error('modal.body_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="body_ar">{{__('pages/modals/index.modal.edit.inputs.body_ar')}}</label>
            <textarea type="text" class="form-control @error('modal.body_ar') is-invalid @enderror"
                      id="body_ar"
                      wire:model.defer="modal.body_ar"
                      ></textarea>
            @error('modal.body_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="link">{{__('pages/modals/index.modal.edit.inputs.link')}}</label>
            <input type="text" class="form-control @error('modal.link') is-invalid @enderror" id="link"
                   name="name"
                   wire:model.defer="modal.link"
            >
            @error('modal.link')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="start_at">{{__('pages/modals/index.modal.edit.inputs.start_at')}}</label>
            <input type="datetime-local" class="form-control @error('modal.start_at') is-invalid @enderror" id="start_at"
                   wire:model.defer="modal.start_at"/>
            @error('modal.start_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="form-group">
            <label for="end_at">{{__('pages/modals/index.modal.edit.inputs.end_at')}}</label>
            <input type="datetime-local" class="form-control @error('modal.end_at') is-invalid @enderror" id="end_at"
                   wire:model.defer="modal.end_at"/>
            @error('modal.end_at')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
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
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->


@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#new_image').val(null);
        });
    </script>
@endpush
