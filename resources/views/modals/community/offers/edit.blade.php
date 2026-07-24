<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$offer['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/community/offers/inquiry.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="content">{{__('pages/community/offers/inquiry.modal.edit.inputs.content')}}</label>
            <textarea rows="7" class="form-control @error('offer.content') is-invalid @enderror" id="content"
                      wire:model.defer="offer.content"></textarea>
            @error('offer.content')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="sale_percentage">{{__('pages/community/offers/inquiry.modal.edit.inputs.sale_percentage')}}</label>
            <input type="number" min="0" step="0.01" class="form-control @error('offer.sale_percentage') is-invalid @enderror"
                   id="sale_percentage"
                   name="sale_percentage"
                   wire:model.defer="offer.sale_percentage">
            @error('offer.sale_percentage')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="advertisement_url">{{__('pages/community/offers/inquiry.modal.edit.inputs.advertisement_url')}}</label>
            <input type="text" class="form-control @error('offer.advertisement_url') is-invalid @enderror"
                   id="advertisement_url"
                   name="advertisement_url"
                   wire:model.defer="offer.advertisement_url">
            @error('offer.advertisement_url')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="expires_in">{{__('pages/community/offers/inquiry.modal.edit.inputs.expires_in')}}</label>
            <input type="number" min="0" step="0.01" class="form-control @error('offer.expires_in') is-invalid @enderror"
                   id="expires_in"
                   name="expires_in"
                   wire:model.defer="offer.expires_in">
            @error('offer.expires_in')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="status">{{__('pages/community/offers/inquiry.modal.edit.inputs.status')}}</label>
            <select class="form-control" id="status" wire:model.defer="offer.status">
                <option value="pending">{{__('pages/community/offers/inquiry.modal.edit.inputs.pending')}}</option>
                <option value="approved">{{__('pages/community/offers/inquiry.modal.edit.inputs.approved')}}</option>
            </select>
            @error('offer.status')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
{{--        <div class="form-group">--}}
{{--            <label for="rate">{{__('pages/community/offers/inquiry.modal.edit.inputs.rate')}}</label>--}}
{{--            <input class="form-control @error('offer.rate') is-invalid @enderror" id="rate"--}}
{{--                   name="rate" type="number" min="0" max="5" step="0.1"--}}
{{--                   wire:model.defer="offer.rate">--}}
{{--            @error('offer.rate')--}}
{{--            <span class="invalid-feedback" role="alert">--}}
{{--                    <strong>{{ $message }}</strong>--}}
{{--                </span>--}}
{{--            @enderror--}}
{{--        </div>--}}
{{--        <div class="form-group">--}}
{{--            <label for="views_count">{{__('pages/community/offers/inquiry.modal.edit.inputs.views_count')}}</label>--}}
{{--            <input type="number" min="0" class="form-control @error('offer.views_count') is-invalid @enderror"--}}
{{--                   id="views_count"--}}
{{--                   name="views_count"--}}
{{--                   wire:model.defer="offer.views_count">--}}
{{--            @error('offer.views_count')--}}
{{--            <span class="invalid-feedback" role="alert">--}}
{{--                    <strong>{{ $message }}</strong>--}}
{{--                </span>--}}
{{--            @enderror--}}
{{--        </div>--}}
{{--        <div class="form-group">--}}
{{--            <label for="likes_count">{{__('pages/community/offers/inquiry.modal.edit.inputs.likes_count')}}</label>--}}
{{--            <input type="number" min="0" class="form-control @error('offer.likes_count') is-invalid @enderror"--}}
{{--                   id="likes_count"--}}
{{--                   name="likes_count"--}}
{{--                   wire:model.defer="offer.likes_count">--}}
{{--            @error('offer.likes_count')--}}
{{--            <span class="invalid-feedback" role="alert">--}}
{{--                    <strong>{{ $message }}</strong>--}}
{{--                </span>--}}
{{--            @enderror--}}
{{--        </div>--}}
{{--        <div class="form-group">--}}
{{--            <label for="comments_count">{{__('pages/community/offers/inquiry.modal.edit.inputs.comments_count')}}</label>--}}
{{--            <input type="number" min="0" class="form-control @error('offer.comments_count') is-invalid @enderror"--}}
{{--                   id="comments_count"--}}
{{--                   name="comments_count"--}}
{{--                   wire:model.defer="offer.comments_count">--}}
{{--            @error('offer.comments_count')--}}
{{--            <span class="invalid-feedback" role="alert">--}}
{{--                    <strong>{{ $message }}</strong>--}}
{{--                </span>--}}
{{--            @enderror--}}
{{--        </div>--}}
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
