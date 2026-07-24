<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$offer['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/community/offers/inquiry.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group" wire:ignore x-data="{category_id: @entangle('category_id').defer,}"
             x-init="$nextTick(() => {
                select2 = $($refs.select).select2({
                    placeholder: '{{__('pages/community/offers/show.modal.edit.inputs.category')}}'
                }).val(category_id ?? null).change();
                select2.on('change', (event) => {
                    category_id = event.target.value;
                });
            })">
            <label for="category_id">{{__('pages/community/posts/inquiry.content.category')}}</label>
            <select x-model="category_id" x-cloak
                    id="category_id"
                    class="form-control select2 @error('category_id') is-invalid @enderror"
                    x-ref="select"
                    x-bind:value="category_id"
                    data-placeholder="{{__('pages/community/offers/show.modal.edit.inputs.category')}}"
                    wire:model.defer="category_id">
                @foreach($categories as $category)
                    @isset($category['children'])
                        <optgroup label="{{$category['name']}}">
                            @foreach($category['children'] as $child)
                                <option @if($category_id == $child['id']) selected
                                        @endif value="{{$child['id']}}">{{$child['name']}}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option @if($category_id == $category['id']) selected
                            @endif value="{{$category['id']}}">{{$category['name']}}</option>
                    @endisset
                @endforeach
            </select>
            @error('category_id')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group">
            <label for="content">{{__('pages/community/offers/inquiry.modal.edit.inputs.content')}}</label>
            <textarea rows="7" class="form-control @error('offerData.content') is-invalid @enderror" id="content"
                      wire:model.defer="offerData.content"></textarea>
            @error('offerData.content')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label
                for="sale_percentage">{{__('pages/community/offers/inquiry.modal.edit.inputs.sale_percentage')}}</label>
            <input type="number" min="0" step="0.01"
                   class="form-control @error('offerData.sale_percentage') is-invalid @enderror"
                   id="sale_percentage"
                   name="sale_percentage"
                   wire:model.defer="offerData.sale_percentage">
            @error('offerData.sale_percentage')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label
                for="advertisement_url">{{__('pages/community/offers/inquiry.modal.edit.inputs.advertisement_url')}}</label>
            <input type="text" class="form-control @error('offerData.advertisement_url') is-invalid @enderror"
                   id="advertisement_url"
                   name="advertisement_url"
                   wire:model.defer="offerData.advertisement_url">
            @error('offerData.advertisement_url')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="expires_at">{{__('pages/community/offers/inquiry.modal.edit.inputs.expires_at')}}</label>
            <input type="datetime-local"
                   class="form-control @error('offerData.expires_at') is-invalid @enderror"
                   id="expires_at"
                   name="expires_at"
                   wire:model.defer="offerData.expires_at">
            @error('offerData.expires_at')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="expires_in">{{__('pages/community/offers/inquiry.modal.edit.inputs.expires_in')}}</label>
            <input type="number" min="0" step="0.01"
                   class="form-control @error('offerData.expires_in') is-invalid @enderror"
                   id="expires_in"
                   name="expires_in"
                   wire:model.defer="offerData.expires_in">
            @error('offerData.expires_in')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="status">{{__('pages/community/offers/inquiry.modal.edit.inputs.status')}}</label>
            <select class="form-control" id="status" wire:model.defer="offerData.status">
                <option value="pending">{{__('pages/community/offers/inquiry.modal.edit.inputs.pending')}}</option>
                <option value="approved">{{__('pages/community/offers/inquiry.modal.edit.inputs.approved')}}</option>
            </select>
            @error('offerData.status')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="rate">{{__('pages/community/offers/inquiry.modal.edit.inputs.rate')}}</label>
            <input class="form-control @error('offerData.rate') is-invalid @enderror" id="rate"
                   name="rate" type="number" min="0" max="5" step="0.1"
                   wire:model.defer="offerData.rate">
            @error('offerData.rate')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="views_count">{{__('pages/community/offers/inquiry.modal.edit.inputs.views_count')}}</label>
            <input type="number" min="0" class="form-control @error('offerData.views_count') is-invalid @enderror"
                   id="views_count"
                   name="views_count"
                   wire:model.defer="offerData.views_count">
            @error('offerData.views_count')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="likes_count">{{__('pages/community/offers/inquiry.modal.edit.inputs.likes_count')}}</label>
            <input type="number" min="0" class="form-control @error('offerData.likes_count') is-invalid @enderror"
                   id="likes_count"
                   name="likes_count"
                   wire:model.defer="offerData.likes_count">
            @error('offerData.likes_count')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label
                for="comments_count">{{__('pages/community/offers/inquiry.modal.edit.inputs.comments_count')}}</label>
            <input type="number" min="0" class="form-control @error('offerData.comments_count') is-invalid @enderror"
                   id="comments_count"
                   name="comments_count"
                   wire:model.defer="offerData.comments_count">
            @error('offerData.comments_count')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group" wire:ignore>
            <label for="media">{{__('pages/community/offers/show.content.media')}}</label>
            <input type="file" class="form-control h-100 @error('media') is-invalid @enderror"
                   id="media"
                   name="media"
                   wire:model.defer="media" multiple>
            @error('media')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{__('pages/community/offers/inquiry.modal.edit.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/community/offers/inquiry.modal.edit.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
