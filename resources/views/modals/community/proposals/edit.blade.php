<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$proposal['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/community/proposals/index.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="content">{{__('pages/community/proposals/index.modal.edit.inputs.content')}}</label>
            <textarea class="form-control @error('proposal.content') is-invalid @enderror" id="content"
                      wire:model.defer="proposal.content"></textarea>
            @error('proposal.content')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="answer">{{__('pages/community/proposals/index.modal.edit.inputs.answer')}}</label>
            <textarea class="form-control @error('proposal.answer') is-invalid @enderror" id="answer"
                      wire:model.defer="proposal.answer"></textarea>
            @error('proposal.answer')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{__('pages/community/proposals/index.modal.edit.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/community/proposals/index.modal.edit.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
