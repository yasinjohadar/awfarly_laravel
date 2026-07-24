<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$rating['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/advertisers/ratings/inquiry.modal.edit.title')}}
    </x-slot>
    <form wire:submit.prevent="update({{$rating['id'] ?? null}})">
        <x-slot name="content">
            <div class="form-group">
                <label for="status">{{__('pages/advertisers/ratings/inquiry.modal.edit.inputs.status')}}</label>
                <select wire:model.defer="status" id="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="approved">{{__('pages/advertisers/ratings/inquiry.modal.edit.inputs.approved')}}</option>
                    <option value="pending">{{__('pages/advertisers/ratings/inquiry.modal.edit.inputs.pending')}}</option>
                    <option value="unapproved">{{__('pages/advertisers/ratings/inquiry.modal.edit.inputs.unapproved')}}</option>
                </select>
                @error('status')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="rate">{{__('pages/advertisers/ratings/inquiry.modal.edit.inputs.rate')}}</label>
                <input type="number" min="1" max="5" step="1" wire:model.defer="rate" id="rate" class="form-control @error('rate') is-invalid @enderror"/>
                @error('comment')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="comment">{{__('pages/advertisers/ratings/inquiry.modal.edit.inputs.comment')}}</label>
                <textarea wire:model.defer="comment" id="comment" class="form-control @error('comment') is-invalid @enderror"></textarea>
                @error('comment')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
                {{__('pages/advertisers/ratings/inquiry.modal.edit.cancel')}}
            </x-secondary-button>

            <x-primary-button wire:loading.attr="disabled" type="submit">
                {{__('pages/advertisers/ratings/inquiry.modal.edit.submit')}}
            </x-primary-button>
        </x-slot>
    </form>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->

