<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$post['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/community/posts/index.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="status">{{__('pages/community/offers/inquiry.modal.edit.inputs.status')}}</label>
            <select class="form-control" id="status" wire:model.defer="post.status">
                <option value="pending">{{__('pages/community/offers/inquiry.modal.edit.inputs.pending')}}</option>
                <option value="approved">{{__('pages/community/offers/inquiry.modal.edit.inputs.approved')}}</option>
            </select>
            @error('post.status')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="views_count">{{__('pages/community/posts/index.modal.edit.inputs.views_count')}}</label>
            <input type="number" min="0" class="form-control @error('post.views_count') is-invalid @enderror"
                   id="views_count"
                   name="views_count"
                   wire:model.defer="post.views_count">
            @error('post.views_count')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="likes_count">{{__('pages/community/posts/index.modal.edit.inputs.likes_count')}}</label>
            <input type="number" min="0" class="form-control @error('post.likes_count') is-invalid @enderror"
                   id="likes_count"
                   name="likes_count"
                   wire:model.defer="post.likes_count">
            @error('post.likes_count')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="comments_count">{{__('pages/community/posts/index.modal.edit.inputs.comments_count')}}</label>
            <input type="number" min="0" class="form-control @error('post.comments_count') is-invalid @enderror"
                   id="comments_count"
                   name="comments_count"
                   wire:model.defer="post.comments_count">
            @error('post.comments_count')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{__('pages/community/posts/index.modal.edit.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/community/posts/index.modal.edit.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
